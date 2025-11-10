<?php
return [
    'class' => yii\db\Connection::class,
    'dsn' => sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST') ?: 'db',
        getenv('DB_PORT') ?: '5432',
        getenv('DB_NAME') ?: 'eco_manager'
    ),
    'username' => getenv('DB_USER') ?: 'eco_user',
    'password' => getenv('DB_PASSWORD') ?: 'eco_password',
    'charset' => 'utf8',
];
