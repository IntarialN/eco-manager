<?php

declare(strict_types=1);

namespace tests\unit;

use app\models\forms\CallbackRequestForm;
use app\models\forms\ChatSessionForm;
use app\services\ChatService;
use tests\support\NotificationServiceStub;
use Yii;
use yii\web\Application;

final class ChatServiceTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $config = require __DIR__ . '/../config/test.php';
        new Application($config);
        Yii::$app->set('notificationService', new NotificationServiceStub());

        require_once Yii::getAlias('@app/migrations/m230000_000010_create_chat_tables.php');
        $migration = new \m230000_000010_create_chat_tables();
        $migration->safeUp();
    }

    protected function tearDown(): void
    {
        if (isset(Yii::$app)) {
            Yii::$app->db->close();
            Yii::$app = null;
        }
        parent::tearDown();
    }

    public function testOpenSessionCreatesInitialMessage(): void
    {
        /** @var ChatService $service */
        $service = Yii::$app->get('chatService');
        $form = new ChatSessionForm([
            'external_contact' => 'user@example.com',
            'name' => 'Иван Петров',
            'initial_message' => 'Нужна консультация',
        ]);

        $session = $service->openSession($form);

        self::assertNotNull($session->id);
        self::assertSame('open', $session->status);
        self::assertSame('web', $session->source);
        self::assertSame('user@example.com', $session->external_contact);
        self::assertNotNull($session->last_message_at);
    }

    public function testCallbackRequestNotifiesSupport(): void
    {
        /** @var ChatService $service */
        $service = Yii::$app->get('chatService');
        $session = $service->openSession(new ChatSessionForm([
            'external_contact' => 'guest@example.com',
            'initial_message' => 'Позвоните мне',
        ]));

        $callbackForm = new CallbackRequestForm([
            'session_id' => $session->id,
            'phone' => '+7 999 000-00-00',
            'preferred_time' => '2025-11-08 10:00:00',
        ]);

        $callback = $service->requestCallback($callbackForm);

        self::assertNotNull($callback->id);
        self::assertSame('pending', $callback->status);
        self::assertSame('pending_callback', $callback->session->status);

        /** @var NotificationServiceStub $stub */
        $stub = Yii::$app->get('notificationService');
        self::assertCount(1, $stub->chats);
        self::assertSame($session->id, $stub->chats[0]['sessionId']);
    }
}
