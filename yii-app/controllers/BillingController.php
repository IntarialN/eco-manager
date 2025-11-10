<?php

namespace app\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class BillingController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'webhook' => ['post'],
                ],
            ],
        ];
    }

    public function actionWebhook(): Response
    {
        $apiKey = Yii::$app->request->headers->get('X-API-Key');
        if ($apiKey !== (Yii::$app->params['bubble']['apiKey'] ?? '')) {
            throw new UnauthorizedHttpException('Invalid API key');
        }

        $payload = Yii::$app->request->bodyParams;
        $event = $payload['event'] ?? null;
        if (!$event) {
            throw new BadRequestHttpException('Missing event type');
        }

        Yii::$app->get('billingSync')->applyInvoiceEvent($event, $payload);

        return $this->asJson(['status' => 'ok']);
    }
}
