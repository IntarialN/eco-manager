<?php

namespace tests\support;

use app\components\NotificationService;
use app\models\Risk;

final class NotificationServiceStub extends NotificationService
{
    public array $events = [];

    public function sendRiskUpdate(Risk $risk, string $event, array $payload = []): void
    {
        $this->events[] = [
            'riskId' => $risk->id,
            'event' => $event,
            'payload' => $payload,
        ];
        parent::sendRiskUpdate($risk, $event, $payload);
    }
}
