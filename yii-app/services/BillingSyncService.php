<?php

namespace app\services;

use app\components\BubbleApiClient;
use app\components\NotificationService;
use app\models\Act;
use app\models\CalendarEvent;
use app\models\Contract;
use app\models\Invoice;
use app\models\Risk;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class BillingSyncService extends Component
{
    /**
     * @var array<int,string> map local client_id => external client identifier
     */
    public array $clientMap = [];

    private BubbleApiClient $apiClient;
    private NotificationService $notificationService;

    public function init(): void
    {
        parent::init();
        $this->apiClient = Yii::$app->get('bubbleApi');
        $this->notificationService = Yii::$app->get('notificationService');

        if (empty($this->clientMap)) {
            throw new InvalidConfigException('BillingSyncService::$clientMap must be configured');
        }
    }

    public function syncAll(): array
    {
        $summary = [
            'contracts' => 0,
            'invoices' => 0,
            'acts' => 0,
        ];

        foreach ($this->clientMap as $clientId => $externalId) {
            $contracts = $this->apiClient->getContracts($externalId);
            foreach ($contracts as $remoteContract) {
                $contract = $this->syncContract($clientId, $remoteContract);
                $summary['contracts']++;
                $summary['invoices'] += $this->syncInvoices($contract, $remoteContract['id']);
                $summary['acts'] += $this->syncActs($contract, $remoteContract['id']);
            }
        }

        return $summary;
    }

    public function applyInvoiceEvent(string $event, array $payload): void
    {
        $invoiceExternalId = $payload['invoice_id'] ?? null;
        if (!$invoiceExternalId) {
            return;
        }

        $invoice = Invoice::findOne(['integration_id' => $invoiceExternalId]);
        if (!$invoice) {
            $data = $this->apiClient->getInvoice($invoiceExternalId);
            if (empty($data)) {
                return;
            }
            $contract = Contract::findOne(['integration_id' => $data['contract_id'] ?? null]);
            if (!$contract) {
                return;
            }
            $invoice = $this->persistInvoice($contract, $data);
        }

        $oldStatus = $invoice->status;
        if ($event === 'invoice.paid') {
            $invoice->status = Invoice::STATUS_PAID;
            $invoice->paid_at = $payload['payment_date'] ?? date('Y-m-d');
        } elseif ($event === 'invoice.overdue') {
            $invoice->status = Invoice::STATUS_OVERDUE;
        }

        if ($invoice->status !== $oldStatus) {
            $invoice->save(false);
            $this->handleInvoiceStatusChange($invoice, $oldStatus);
        }
    }

    private function syncContract(int $clientId, array $data): Contract
    {
        $contract = Contract::findOne(['integration_id' => $data['id'] ?? null]);
        if (!$contract) {
            $contract = new Contract([
                'client_id' => $clientId,
                'integration_id' => $data['id'] ?? null,
            ]);
        }

        $contract->client_id = $clientId;
        $contract->client_external_id = $data['client_external_id'] ?? $contract->client_external_id;
        $contract->number = $data['number'] ?? $contract->number ?? '';
        $contract->title = $data['title'] ?? $contract->title ?? '';
        $contract->status = $data['status'] ?? $contract->status ?? Contract::STATUS_DRAFT;
        $contract->amount = $data['total_amount'] ?? $contract->amount ?? 0;
        $contract->currency = $data['currency'] ?? $contract->currency ?? 'RUB';
        $contract->signed_at = $data['valid_from'] ?? $contract->signed_at;
        $contract->valid_from = $data['valid_from'] ?? $contract->valid_from;
        $contract->valid_until = $data['valid_until'] ?? $contract->valid_until;
        $contract->integration_revision = $data['updated_at'] ?? $contract->integration_revision;
        $contract->save(false);

        return $contract;
    }

    private function syncInvoices(Contract $contract, string $contractExternalId): int
    {
        $count = 0;
        $invoices = $this->apiClient->getInvoices($contractExternalId);
        foreach ($invoices as $invoiceData) {
            $this->persistInvoice($contract, $invoiceData);
            $count++;
        }

        return $count;
    }

    private function persistInvoice(Contract $contract, array $data): Invoice
    {
        $invoice = Invoice::findOne(['integration_id' => $data['id'] ?? null]);
        if (!$invoice) {
            $invoice = new Invoice([
                'contract_id' => $contract->id,
                'integration_id' => $data['id'] ?? null,
            ]);
        }

        $oldStatus = $invoice->status ?? Invoice::STATUS_ISSUED;

        $invoice->contract_id = $contract->id;
        $invoice->number = $data['number'] ?? $invoice->number ?? '';
        $invoice->status = $data['status'] ?? $invoice->status ?? Invoice::STATUS_ISSUED;
        $invoice->amount = $data['amount'] ?? $invoice->amount ?? 0;
        $invoice->issued_at = $data['issue_date'] ?? $invoice->issued_at;
        $invoice->due_date = $data['due_date'] ?? $invoice->due_date;
        $invoice->paid_at = $data['payment_date'] ?? $invoice->paid_at;
        $invoice->currency = $data['currency'] ?? $invoice->currency ?? 'RUB';
        $invoice->save(false);

        if ($oldStatus !== $invoice->status) {
            $this->handleInvoiceStatusChange($invoice, $oldStatus);
        }

        return $invoice;
    }

    private function syncActs(Contract $contract, string $contractExternalId): int
    {
        $acts = $this->apiClient->getActs($contractExternalId);
        $count = 0;

        foreach ($acts as $actData) {
            $act = Act::findOne(['integration_id' => $actData['id'] ?? null]);
            if (!$act) {
                $act = new Act([
                    'integration_id' => $actData['id'] ?? null,
                ]);
            }

            $act->contract_id = $contract->id;
            $act->number = $actData['number'] ?? $act->number ?? '';
            $act->status = $actData['status'] ?? $act->status ?? Act::STATUS_DRAFT;
            $act->issued_at = $actData['issue_date'] ?? $act->issued_at;
            $invoiceExternalId = $actData['invoice_id'] ?? null;
            if ($invoiceExternalId) {
                $invoice = Invoice::findOne(['integration_id' => $invoiceExternalId]);
                if ($invoice) {
                    $act->invoice_id = $invoice->id;
                }
            }
            $act->integration_revision = $actData['updated_at'] ?? $act->integration_revision;
            $act->save(false);
            $count++;
        }

        return $count;
    }

    private function handleInvoiceStatusChange(Invoice $invoice, string $oldStatus): void
    {
        $this->notificationService->sendInvoiceStatusChange($invoice, $oldStatus);
        $this->syncInvoiceCalendarEvent($invoice);
        $this->syncInvoiceRisk($invoice);
    }

    private function syncInvoiceCalendarEvent(Invoice $invoice): void
    {
        $contract = $invoice->contract;
        if (!$contract) {
            return;
        }

        $title = sprintf('Оплата счёта %s', $invoice->number);
        $event = CalendarEvent::find()
            ->where([
                'client_id' => $contract->client_id,
                'title' => $title,
                'type' => 'invoice',
            ])
            ->one();

        if (!$event) {
            $event = new CalendarEvent([
                'client_id' => $contract->client_id,
                'title' => $title,
                'type' => 'invoice',
            ]);
        }

        $event->due_date = $invoice->due_date ?: ($invoice->issued_at ?? date('Y-m-d'));
        if (!$event->start_date) {
            $event->start_date = $event->due_date;
        }
        $event->periodicity = CalendarEvent::PERIOD_ONCE;

        if ($invoice->status === Invoice::STATUS_PAID) {
            $event->status = CalendarEvent::STATUS_DONE;
            $event->completed_at = $invoice->paid_at ?: date('Y-m-d');
        } elseif ($invoice->status === Invoice::STATUS_OVERDUE) {
            $event->status = CalendarEvent::STATUS_OVERDUE;
            $event->completed_at = null;
        } else {
            $event->status = CalendarEvent::STATUS_SCHEDULED;
            $event->completed_at = null;
        }

        $event->save(false);
    }

    private function syncInvoiceRisk(Invoice $invoice): void
    {
        $contract = $invoice->contract;
        if (!$contract) {
            return;
        }

        $title = sprintf('Просрочка оплаты счёта %s', $invoice->number);
        $risk = Risk::find()
            ->where(['client_id' => $contract->client_id, 'title' => $title])
            ->one();

        if ($invoice->status === Invoice::STATUS_OVERDUE) {
            if (!$risk) {
                $risk = new Risk([
                    'client_id' => $contract->client_id,
                    'title' => $title,
                    'severity' => 'medium',
                    'status' => Risk::STATUS_OPEN,
                    'description' => 'Счёт неоплачен в срок. Возможен штраф или пеня.',
                    'detected_at' => date('Y-m-d'),
                ]);
                $risk->save(false);
            } elseif ($risk->status !== Risk::STATUS_OPEN) {
                $risk->status = Risk::STATUS_OPEN;
                $risk->resolved_at = null;
                $risk->save(false, ['status', 'resolved_at']);
            }
        } elseif ($risk) {
            $risk->status = Risk::STATUS_CLOSED;
            $risk->resolved_at = date('Y-m-d');
            $risk->save(false, ['status', 'resolved_at']);
        }
    }
}
