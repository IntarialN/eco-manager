<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

return [
    'id' => 'eco-manager-portal',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'sourceLanguage' => 'en-US',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'replace-with-secret-key',
            'parsers' => [
                'application/json' => yii\web\JsonParser::class,
            ],
        ],
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => app\models\User::class,
            'enableAutoLogin' => true,
            'enableSession' => true,
            'loginUrl' => ['site/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => yii\symfonymailer\Mailer::class,
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'client/select',
                'client/select' => 'client/select',
                'client/onboard' => 'client/onboard',
                'client/onboard-self' => 'client/onboard-self',
                'client/manager-list' => 'client/manager-list',
                'client/<id:\d+>' => 'client/view',
                'site/<action:\w+>' => 'site/<action>',
                'billing/webhook' => 'billing/webhook',
                'chat/inbox' => 'chat/inbox',
                'chat/thread/<id:\d+>' => 'chat/thread',
                ['pattern' => 'chat/<id:\d+>/assign', 'route' => 'chat/assign', 'verb' => 'POST'],
                ['pattern' => 'chat/<id:\d+>/reply', 'route' => 'chat/reply', 'verb' => 'POST'],
                ['pattern' => 'chat/session', 'route' => 'chat/create', 'verb' => 'POST'],
                ['pattern' => 'chat/<id:\d+>/message', 'route' => 'chat/message', 'verb' => 'POST'],
                ['pattern' => 'chat/<id:\d+>/callback', 'route' => 'chat/callback', 'verb' => 'POST'],
                ['pattern' => 'chat/<id:\d+>/stream', 'route' => 'chat/stream', 'verb' => 'GET'],
                ['pattern' => 'chat/<id:\d+>', 'route' => 'chat/view', 'verb' => 'GET'],
            ],
        ],
        'assetManager' => [
            'appendTimestamp' => true,
            'basePath' => '@app/web/assets',
            'baseUrl' => '@web/assets',
        ],
        'formatter' => [
            'locale' => 'ru-RU',
            'defaultTimeZone' => 'Europe/Moscow',
            'dateFormat' => 'php:d F Y',
            'datetimeFormat' => 'php:d F Y H:i',
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
        'requirementBuilder' => [
            'class' => app\components\RequirementBuilderService::class,
        ],
        'chatService' => [
            'class' => app\services\ChatService::class,
        ],
    ],
    'modules' => [],
    'params' => $params,
];
