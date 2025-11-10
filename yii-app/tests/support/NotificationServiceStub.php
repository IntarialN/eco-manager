<?php

namespace tests\support;

use app\components\NotificationService;
use app\models\CallbackRequest;
use app\models\CalendarEvent;
use app\models\ChatSession;
use app\models\Document;
use app\models\Invoice;
use app\models\Risk;

final class NotificationServiceStub extends NotificationService
{
    public array $events = [];
    public array $documents = [];
    public array $calendars = [];
    public array $chats = [];

    public function sendRiskUpdate(Risk $risk, string $event, array $payload = []): void
    {
        $this->events[] = [
            'type' => 'risk',
            'riskId' => $risk->id,
            'event' => $event,
            'payload' => $payload,
        ];
    }

    public function sendInvoiceStatusChange(Invoice $invoice, string $oldStatus): void
    {
        $this->events[] = [
            'type' => 'invoice',
            'invoiceId' => $invoice->id,
            'oldStatus' => $oldStatus,
            'newStatus' => $invoice->status,
        ];
    }

    public function sendDocumentUploaded(Document $document): void
    {
        $this->documents[] = [
            'documentId' => $document->id,
            'requirementId' => $document->requirement_id,
        ];
    }

    public function sendCalendarEventStatus(CalendarEvent $event): void
    {
        $this->calendars[] = [
            'eventId' => $event->id,
            'status' => $event->status,
        ];
    }

    public function sendChatCallbackRequest(ChatSession $session, CallbackRequest $callback): void
    {
        $this->chats[] = [
            'sessionId' => $session->id,
            'phone' => $callback->phone,
        ];
    }

    protected function notify(string $category, string $subject, string $body): void
    {
        // suppress mailer calls in tests
    }
}
