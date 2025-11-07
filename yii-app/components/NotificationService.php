<?php

namespace app\components;

use app\models\Risk;
use Yii;

class NotificationService
{
    public function sendRiskUpdate(Risk $risk, string $event, array $payload = []): void
    {
        Yii::info([
            'type' => 'risk_update',
            'event' => $event,
            'riskId' => $risk->id,
            'clientId' => $risk->client_id,
            'payload' => $payload,
        ], __METHOD__);
    }
}
