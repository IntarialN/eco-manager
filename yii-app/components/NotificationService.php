<?php

namespace app\components;

use app\models\CallbackRequest;
use app\models\CalendarEvent;
use app\models\ChatSession;
use app\models\Document;
use app\models\Invoice;
use app\models\Risk;
use Yii;
use yii\base\Component;

class NotificationService extends Component
{
    /**
     * @var array<string,string> category => email
     */
    public array $emails = [];

    public function sendRiskUpdate(Risk $risk, string $event, array $payload = []): void
    {
        $message = sprintf('Риск #%d обновлён: событие %s', $risk->id, $event);
        $this->notify('risk', 'Обновление риска', $message);

        Yii::info([
            'type' => 'risk_update',
            'event' => $event,
            'riskId' => $risk->id,
            'clientId' => $risk->client_id,
            'payload' => $payload,
        ], __METHOD__);
    }

    public function sendInvoiceStatusChange(Invoice $invoice, string $oldStatus): void
    {
        $subject = sprintf('Статус счёта %s изменён', $invoice->number);
        $body = sprintf(
            "Счёт %s перешёл из статуса \"%s\" в \"%s\".\nСумма: %s %s\nДоговор ID: %d",
            $invoice->number,
            Invoice::statusLabels()[$oldStatus] ?? $oldStatus,
            Invoice::statusLabels()[$invoice->status] ?? $invoice->status,
            number_format((float)$invoice->amount, 2, '.', ' '),
            $invoice->currency,
            $invoice->contract_id
        );
        $this->notify('finance', $subject, $body);

        Yii::info([
            'type' => 'invoice_status',
            'invoiceId' => $invoice->id,
            'contractId' => $invoice->contract_id,
            'oldStatus' => $oldStatus,
            'newStatus' => $invoice->status,
        ], __METHOD__);
    }

    public function sendDocumentUploaded(Document $document): void
    {
        $subject = sprintf('Загружен документ "%s"', $document->title);
        $body = sprintf(
            "Документ \"%s\" (%s) загружен по требованию #%d.\nРежим: %s\nСтатус: %s",
            $document->title,
            $document->type,
            $document->requirement_id,
            $document->getReviewModeLabel(),
            $document->getStatusLabel()
        );
        $this->notify('operations', $subject, $body);

        Yii::info([
            'type' => 'document_uploaded',
            'documentId' => $document->id,
            'requirementId' => $document->requirement_id,
        ], __METHOD__);
    }

    public function sendCalendarEventStatus(CalendarEvent $event): void
    {
        $subject = sprintf('Событие календаря "%s" обновлено', $event->title);
        $body = sprintf(
            "Событие \"%s\" (клиент #%d) теперь имеет статус \"%s\". Дата: %s",
            $event->title,
            $event->client_id,
            CalendarEvent::statusLabels()[$event->status] ?? $event->status,
            $event->due_date
        );
        $this->notify('operations', $subject, $body);
    }

    public function sendChatCallbackRequest(ChatSession $session, CallbackRequest $callback): void
    {
        $subject = sprintf('Запрос обратного звонка, сессия #%d', $session->id);
        $contact = $session->external_contact ?: ($session->client->name ?? 'не указан');
        $body = sprintf(
            "Новый запрос звонка.\nСессия #%d, источник: %s\nКонтакт: %s\nТелефон: %s\nЖелаемое время: %s\nКомментарий: %s",
            $session->id,
            $session->source,
            $contact,
            $callback->phone,
            $callback->preferred_time ?? 'не указано',
            $callback->comment ?? '—'
        );
        $this->notify('support', $subject, $body);

        Yii::info([
            'type' => 'chat_callback',
            'sessionId' => $session->id,
            'phone' => $callback->phone,
        ], __METHOD__);
    }

    protected function notify(string $category, string $subject, string $body): void
    {
        $to = $this->emails[$category] ?? $this->emails['default'] ?? null;
        if (!$to || !Yii::$app->has('mailer')) {
            Yii::info([
                'category' => $category,
                'subject' => $subject,
                'body' => $body,
            ], __METHOD__);
            return;
        }

        try {
            Yii::$app->mailer->compose()
                ->setTo($to)
                ->setSubject($subject)
                ->setTextBody($body)
                ->send();
        } catch (\Throwable $e) {
            Yii::error(['message' => 'Notification send failed', 'error' => $e->getMessage()], __METHOD__);
        }
    }
}
