<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $session_id
 * @property string $phone
 * @property string|null $preferred_time
 * @property string $status
 * @property string|null $comment
 * @property string|null $processed_at
 * @property string $created_at
 *
 * @property ChatSession $session
 */
class CallbackRequest extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName(): string
    {
        return '{{%callback_request}}';
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
            [['session_id', 'phone'], 'required'],
            [['session_id'], 'integer'],
            [['preferred_time', 'processed_at', 'created_at'], 'safe'],
            [['comment'], 'string'],
            [['phone'], 'string', 'max' => 32],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['session_id'], 'exist', 'targetClass' => ChatSession::class, 'targetAttribute' => ['session_id' => 'id']],
        ];
    }

    public function getSession()
    {
        return $this->hasOne(ChatSession::class, ['id' => 'session_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает обработки',
            self::STATUS_IN_PROGRESS => 'В процессе',
            self::STATUS_DONE => 'Выполнен',
            self::STATUS_CANCELLED => 'Отменён',
        ];
    }
}
