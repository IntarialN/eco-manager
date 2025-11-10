<?php

namespace app\models\forms;

use app\models\ChatMessage;
use app\models\ChatSession;
use yii\base\Model;

class ChatMessageForm extends Model
{
    public int $session_id;
    public string $sender_type = ChatMessage::SENDER_CLIENT;
    public ?int $sender_id = null;
    public string $direction = ChatMessage::DIRECTION_WEB_TO_BOT;
    public string $body = '';
    public array $attachments = [];

    public function rules(): array
    {
        return [
            [['session_id', 'sender_type', 'body'], 'required'],
            [['session_id', 'sender_id'], 'integer'],
            [['body'], 'string'],
            [['attachments'], 'each', 'rule' => ['string']],
            [['sender_type'], 'in', 'range' => array_keys(ChatMessage::senderLabels())],
            [['direction'], 'in', 'range' => array_keys(ChatMessage::directionLabels())],
            ['session_id', 'exist', 'targetClass' => ChatSession::class, 'targetAttribute' => ['session_id' => 'id']],
        ];
    }
}
