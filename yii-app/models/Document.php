<?php
namespace app\models;

use yii\db\ActiveRecord;

class Document extends ActiveRecord
{
    public const STATUS_PENDING = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName(): string
    {
        return '{{%document}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'title', 'type'], 'required'],
            [['client_id', 'requirement_id'], 'integer'],
            [['uploaded_at'], 'safe'],
            [['path'], 'string'],
            [['status', 'type'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 255],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getRequirement()
    {
        return $this->hasOne(Requirement::class, ['id' => 'requirement_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'На проверке',
            self::STATUS_APPROVED => 'Подтверждён',
            self::STATUS_REJECTED => 'Отклонён',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
