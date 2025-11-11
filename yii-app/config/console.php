<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

return [
    'id' => 'eco-manager-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'i18n' => [
            'translations' => [
                'yii/bootstrap5*' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@vendor/yiisoft/yii2-bootstrap5/messages',
                ],
            ],
        ],
        'notificationService' => [
            'class' => app\components\NotificationService::class,
            'emails' => $params['notifications'],
        ],
        'bubbleApi' => [
            'class' => app\components\BubbleApiClient::class,
            'baseUrl' => $params['bubble']['baseUrl'],
            'apiKey' => $params['bubble']['apiKey'],
        ],
        'billingSync' => [
            'class' => app\services\BillingSyncService::class,
            'clientMap' => $params['bubble']['clientMap'],
        ],
        'chatService' => [
            'class' => app\services\ChatService::class,
        ],
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => yii\console\controllers\MigrateController::class,
            'migrationPath' => '@app/migrations',
        ],
    ],
    'params' => $params,
];
