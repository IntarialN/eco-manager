<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $session_id
 * @property string $sender_type
 * @property int|null $sender_id
 * @property string $direction
 * @property string $body
 * @property string|null $attachments
 * @property string|null $delivered_at
 * @property string|null $read_at
 * @property string $created_at
 *
 * @property ChatSession $session
 */
class ChatMessage extends ActiveRecord
{
    public const SENDER_CLIENT = 'client';
    public const SENDER_OPERATOR = 'operator';
    public const SENDER_SYSTEM = 'system';

    public const DIRECTION_WEB_TO_BOT = 'web_to_bot';
    public const DIRECTION_BOT_TO_WEB = 'bot_to_web';

    public static function tableName(): string
    {
        return '{{%chat_message}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('CURRENT_TIMESTAMP'),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['session_id', 'sender_type', 'body'], 'required'],
            [['session_id', 'sender_id'], 'integer'],
            [['body', 'attachments'], 'string'],
            [['delivered_at', 'read_at', 'created_at'], 'safe'],
            [['sender_type'], 'in', 'range' => array_keys(self::senderLabels())],
            [['direction'], 'in', 'range' => array_keys(self::directionLabels())],
            [['session_id'], 'exist', 'targetClass' => ChatSession::class, 'targetAttribute' => ['session_id' => 'id']],
        ];
    }

    public function getSession()
    {
        return $this->hasOne(ChatSession::class, ['id' => 'session_id']);
    }

    public static function senderLabels(): array
    {
        return [
            self::SENDER_CLIENT => 'Клиент',
            self::SENDER_OPERATOR => 'Оператор',
            self::SENDER_SYSTEM => 'Система',
        ];
    }

    public static function directionLabels(): array
    {
        return [
            self::DIRECTION_WEB_TO_BOT => 'Клиент → оператор',
            self::DIRECTION_BOT_TO_WEB => 'Оператор → клиент',
        ];
    }
}
