<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int|null $client_id
 * @property string|null $external_contact
 * @property string|null $name
 * @property string $source
 * @property string $initiator
 * @property string $status
 * @property int|null $assigned_user_id
 * @property string|null $last_message_at
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $assigned_seen_at
 *
 * @property User|null $assignedUser
 * @property Client|null $client
 * @property ChatMessage[] $messages
 * @property CallbackRequest[] $callbackRequests
 */
class ChatSession extends ActiveRecord
{
    public const STATUS_OPEN = 'open';
    public const STATUS_PENDING_CALLBACK = 'pending_callback';
    public const STATUS_CLOSED = 'closed';

    public const SOURCE_WEB = 'web';
    public const SOURCE_TELEGRAM = 'telegram';
    public const SOURCE_CALLBACK = 'callback';

    public const INITIATOR_CLIENT = 'client';
    public const INITIATOR_OPERATOR = 'operator';

    public static function tableName(): string
    {
        return '{{%chat_session}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('CURRENT_TIMESTAMP'),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['source', 'initiator'], 'required'],
            [['client_id', 'assigned_user_id'], 'integer'],
            [['last_message_at', 'created_at', 'updated_at'], 'safe'],
            [['assigned_seen_at'], 'safe'],
            [['external_contact', 'name'], 'string', 'max' => 128],
            [['source'], 'in', 'range' => array_keys(self::sourceLabels())],
            [['initiator'], 'in', 'range' => array_keys(self::initiatorLabels())],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id'], 'skipOnEmpty' => true],
            [['assigned_user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['assigned_user_id' => 'id'], 'skipOnEmpty' => true],
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getAssignedUser()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_user_id']);
    }

    public function getMessages()
    {
        return $this->hasMany(ChatMessage::class, ['session_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }

    public function getCallbackRequests()
    {
        return $this->hasMany(CallbackRequest::class, ['session_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => 'Открыт',
            self::STATUS_PENDING_CALLBACK => 'Ожидает звонка',
            self::STATUS_CLOSED => 'Закрыт',
        ];
    }

    public static function openStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_PENDING_CALLBACK,
        ];
    }

    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_WEB => 'Веб',
            self::SOURCE_TELEGRAM => 'Telegram',
            self::SOURCE_CALLBACK => 'Обратный звонок',
        ];
    }

    public static function initiatorLabels(): array
    {
        return [
            self::INITIATOR_CLIENT => 'Клиент',
            self::INITIATOR_OPERATOR => 'Оператор',
        ];
    }
}
