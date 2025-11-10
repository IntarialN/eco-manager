<?php

declare(strict_types=1);

return [
    'id' => 'eco-manager-tests',
    'basePath' => dirname(__DIR__, 2),
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'components' => [
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => 'sqlite::memory:',
        ],
        'request' => [
            'class' => yii\web\Request::class,
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
        ],
        'session' => [
            'class' => tests\support\ArraySession::class,
        ],
        'user' => [
            'class' => yii\web\User::class,
            'identityClass' => app\models\User::class,
            'enableAutoLogin' => false,
            'enableSession' => true,
            'loginUrl' => null,
        ],
        'security' => [
            'class' => yii\base\Security::class,
        ],
        'mailer' => [
            'class' => yii\symfonymailer\Mailer::class,
            'useFileTransport' => true,
        ],
        'requirementBuilder' => [
            'class' => app\components\RequirementBuilderService::class,
        ],
        'chatService' => [
            'class' => app\services\ChatService::class,
        ],
    ],
    'params' => [
        'registration' => [
            'allowedRoles' => [
                \app\models\User::ROLE_CLIENT_USER,
                \app\models\User::ROLE_CLIENT_MANAGER,
            ],
        ],
        'supportEmail' => 'support@example.com',
    ],
];
