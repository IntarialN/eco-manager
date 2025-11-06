<?php

declare(strict_types=1);

namespace tests\unit;

use app\controllers\RequirementController;
use app\controllers\RiskController;
use app\models\CalendarEvent;
use app\models\Requirement;
use app\models\Risk;
use app\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\Application;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

abstract class ControllerTestCase extends \PHPUnit\Framework\TestCase
{
    private string $uploadDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        $this->createSchema();
        $this->seedBaseData();
    }

    protected function tearDown(): void
    {
        if (isset(Yii::$app)) {
            Yii::$app->db->close();
            Yii::$app = null;
        }

        if (isset($this->uploadDir) && is_dir($this->uploadDir)) {
            $this->removeDirectory($this->uploadDir);
        }

        $_POST = [];
        $_FILES = [];
        $_GET = [];
        UploadedFile::reset();

        parent::tearDown();
    }

    protected function runControllerAction(
        string $controllerId,
        string $actionId,
        array $params = [],
        array $post = [],
        array $files = []
    ): void {
        $_POST = $post;
        $_FILES = $files;
        $_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
        Yii::$app->request->setQueryParams($params);
        Yii::$app->request->setBodyParams($post);

        $controller = $this->instantiateController($controllerId);
        $previousController = Yii::$app->controller;
        Yii::$app->controller = $controller;

        try {
            $controller->runAction($actionId, $params);
        } finally {
            Yii::$app->controller = $previousController;
        }
    }

    private function instantiateController(string $controllerId): Controller
    {
        return match ($controllerId) {
            'requirement' => new RequirementController('requirement', Yii::$app),
            'risk' => new RiskController('risk', Yii::$app),
            default => throw new InvalidArgumentException("Unknown controller: {$controllerId}"),
        };
    }

    private function mockApplication(): void
    {
        $config = require __DIR__ . '/../config/test.php';
        $config['components']['response'] = [
            'class' => Response::class,
        ];
        $config['components']['urlManager'] = [
            'class' => \yii\web\UrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ];

        new Application($config);

        $this->uploadDir = sys_get_temp_dir() . '/eco-manager-uploads-' . uniqid('', true);
        Yii::setAlias('@app/web/uploads', $this->uploadDir);
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    private function createSchema(): void
    {
        $db = Yii::$app->db;
        $commands = [
            'CREATE TABLE client (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                registration_number TEXT
            )',
            'CREATE TABLE user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER,
                username TEXT NOT NULL,
                email TEXT NOT NULL,
                role TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                auth_key TEXT NOT NULL,
                is_active INTEGER NOT NULL DEFAULT 1
            )',
            'CREATE TABLE requirement (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER NOT NULL,
                site_id INTEGER,
                code TEXT NOT NULL,
                title TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT "new",
                due_date TEXT,
                completed_at TEXT,
                category TEXT
            )',
            'CREATE TABLE risk (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER NOT NULL,
                requirement_id INTEGER,
                title TEXT NOT NULL,
                severity TEXT NOT NULL,
                status TEXT,
                description TEXT,
                detected_at TEXT,
                resolved_at TEXT
            )',
            'CREATE TABLE calendar_event (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER NOT NULL,
                requirement_id INTEGER,
                title TEXT NOT NULL,
                type TEXT,
                status TEXT,
                due_date TEXT,
                completed_at TEXT
            )',
            'CREATE TABLE requirement_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                requirement_id INTEGER NOT NULL,
                user_id INTEGER,
                old_status TEXT,
                new_status TEXT NOT NULL,
                comment TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )',
            'CREATE TABLE document (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER NOT NULL,
                requirement_id INTEGER,
                title TEXT NOT NULL,
                type TEXT NOT NULL,
                status TEXT NOT NULL,
                path TEXT,
                uploaded_at TEXT
            )',
            'CREATE TABLE risk_action_plan (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                risk_id INTEGER NOT NULL,
                task TEXT NOT NULL,
                owner_id INTEGER,
                status TEXT NOT NULL DEFAULT "new",
                due_date TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT
            )',
            'CREATE TABLE risk_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                risk_id INTEGER NOT NULL,
                user_id INTEGER,
                action TEXT NOT NULL,
                notes TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )',
        ];

        foreach ($commands as $command) {
            $db->createCommand($command)->execute();
        }
    }

    private function seedBaseData(): void
    {
        $db = Yii::$app->db;
        $db->createCommand()->insert('client', [
            'id' => 1,
            'name' => 'ООО Экопример',
            'registration_number' => '7700000000',
        ])->execute();

        $db->createCommand()->insert('user', [
            'id' => 1,
            'client_id' => null,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
            'password_hash' => 'hash',
            'auth_key' => 'testkey',
            'is_active' => 1,
        ])->execute();

        $db->createCommand()->insert('user', [
            'id' => 2,
            'client_id' => null,
            'username' => 'manager',
            'email' => 'manager@example.com',
            'role' => User::ROLE_CLIENT_MANAGER,
            'password_hash' => 'hash',
            'auth_key' => 'testkey2',
            'is_active' => 1,
        ])->execute();

        $db->createCommand()->insert('requirement', [
            'id' => 1,
            'client_id' => 1,
            'code' => 'REQ-01',
            'title' => 'Сдать отчёт',
            'status' => Requirement::STATUS_NEW,
            'due_date' => '2099-01-01',
            'category' => 'waste',
        ])->execute();

        $db->createCommand()->insert('risk', [
            'id' => 1,
            'client_id' => 1,
            'requirement_id' => 1,
            'title' => 'Штраф за просрочку',
            'severity' => 'high',
            'status' => Risk::STATUS_OPEN,
        ])->execute();

        $db->createCommand()->batchInsert('calendar_event', [
            'id',
            'client_id',
            'requirement_id',
            'title',
            'type',
            'status',
            'due_date',
        ], [
            [1, 1, 1, 'Просроченный дедлайн', 'report', CalendarEvent::STATUS_SCHEDULED, '2000-01-01'],
            [2, 1, 1, 'Будущий дедлайн', 'report', CalendarEvent::STATUS_SCHEDULED, '2099-01-01'],
        ])->execute();

        Yii::$app->session->open();
        $user = User::findOne(1);
        Yii::$app->user->login($user);
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                $this->removeDirectory($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        rmdir($path);
    }
}
