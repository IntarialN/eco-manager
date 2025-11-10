<?php
namespace app\models;

use yii\db\ActiveRecord;

class Document extends ActiveRecord
{
    public const STATUS_PENDING = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const REVIEW_MODE_STORAGE = 'storage';
    public const REVIEW_MODE_AUDIT = 'audit';

    public static function tableName(): string
    {
        return '{{%document}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'title', 'type', 'review_mode'], 'required'],
            [['client_id', 'requirement_id', 'auditor_id'], 'integer'],
            [['uploaded_at', 'audit_completed_at'], 'safe'],
            [['path'], 'string'],
            [['status', 'type'], 'string', 'max' => 50],
            [['review_mode'], 'in', 'range' => array_keys(self::reviewModeLabels())],
            [['title'], 'string', 'max' => 255],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
            [['auditor_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['auditor_id' => 'id'], 'skipOnEmpty' => true],
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

    public function getAuditor()
    {
        return $this->hasOne(User::class, ['id' => 'auditor_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'На проверке',
            self::STATUS_APPROVED => 'Подтверждён',
            self::STATUS_REJECTED => 'Отклонён',
        ];
    }

    public static function reviewModeLabels(): array
    {
        return [
            self::REVIEW_MODE_STORAGE => 'Без аудита (самостоятельная загрузка)',
            self::REVIEW_MODE_AUDIT => 'С аудитом специалиста',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getReviewModeLabel(): string
    {
        return self::reviewModeLabels()[$this->review_mode] ?? $this->review_mode;
    }
}
