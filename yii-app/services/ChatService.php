<?php

namespace app\services;

use app\components\NotificationService;
use app\models\CallbackRequest;
use app\models\ChatMessage;
use app\models\ChatSession;
use app\models\forms\CallbackRequestForm;
use app\models\forms\ChatMessageForm;
use app\models\forms\ChatSessionForm;
use RuntimeException;
use Yii;
use yii\base\Component;
use yii\helpers\Json;

class ChatService extends Component
{
    private NotificationService $notificationService;

    public function init(): void
    {
        parent::init();
        $this->notificationService = Yii::$app->get('notificationService');
    }

    public function openSession(ChatSessionForm $form): ChatSession
    {
        if (!$form->validate()) {
            throw new RuntimeException('ChatSessionForm validation failed: ' . Json::encode($form->errors, JSON_THROW_ON_ERROR));
        }

        $session = new ChatSession([
            'client_id' => $form->client_id,
            'external_contact' => $form->external_contact,
            'name' => $form->name,
            'source' => $form->source,
            'initiator' => $form->initiator,
            'status' => $form->status,
        ]);

        if (!$session->save()) {
            throw new RuntimeException('Failed to create chat session: ' . Json::encode($session->errors, JSON_THROW_ON_ERROR));
        }

        if ($form->initial_message) {
            $messageForm = new ChatMessageForm([
                'session_id' => $session->id,
                'sender_type' => ChatMessage::SENDER_CLIENT,
                'sender_id' => $session->client_id,
                'direction' => ChatMessage::DIRECTION_WEB_TO_BOT,
                'body' => $form->initial_message,
            ]);
            $this->postMessage($messageForm);
            $session->refresh();
        }

        return $session;
    }

    public function postMessage(ChatMessageForm $form): ChatMessage
    {
        if (!$form->validate()) {
            throw new RuntimeException('ChatMessageForm validation failed: ' . Json::encode($form->errors, JSON_THROW_ON_ERROR));
        }

        $session = ChatSession::findOne($form->session_id);
        if (!$session) {
            throw new RuntimeException('Chat session not found');
        }

        $message = new ChatMessage([
            'session_id' => $form->session_id,
            'sender_type' => $form->sender_type,
            'sender_id' => $form->sender_id,
            'direction' => $form->direction,
            'body' => $form->body,
            'attachments' => $form->attachments ? Json::encode($form->attachments, JSON_UNESCAPED_UNICODE) : null,
        ]);

        if (!$message->save()) {
            throw new RuntimeException('Failed to persist chat message: ' . Json::encode($message->errors, JSON_THROW_ON_ERROR));
        }

        $message->refresh();
        $session->last_message_at = date('Y-m-d H:i:s');
        if ($form->sender_type === ChatMessage::SENDER_OPERATOR
            && $session->assigned_user_id
            && (int)$session->assigned_user_id === (int)$form->sender_id) {
            $session->assigned_seen_at = $session->last_message_at;
        } elseif ($form->sender_type === ChatMessage::SENDER_CLIENT) {
            $session->assigned_user_id = null;
            $session->assigned_seen_at = null;
        }
        $session->status = $session->status === ChatSession::STATUS_PENDING_CALLBACK
            ? ChatSession::STATUS_PENDING_CALLBACK
            : ChatSession::STATUS_OPEN;
        $session->save(false, ['last_message_at', 'status', 'assigned_user_id', 'assigned_seen_at', 'updated_at']);

        return $message;
    }

    public function requestCallback(CallbackRequestForm $form): CallbackRequest
    {
        if (!$form->validate()) {
            throw new RuntimeException('CallbackRequestForm validation failed: ' . Json::encode($form->errors, JSON_THROW_ON_ERROR));
        }

        $session = ChatSession::findOne($form->session_id);
        if (!$session) {
            throw new RuntimeException('Chat session not found');
        }

        $callback = new CallbackRequest($form->toAttributes());
        if (!$callback->save()) {
            throw new RuntimeException('Failed to save callback request: ' . Json::encode($callback->errors, JSON_THROW_ON_ERROR));
        }

        $session->status = ChatSession::STATUS_PENDING_CALLBACK;
        $session->save(false, ['status', 'updated_at']);

        $this->notificationService->sendChatCallbackRequest($session, $callback);

        return $callback;
    }
}
