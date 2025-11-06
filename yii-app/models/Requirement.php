<?php
namespace app\models;

use yii\db\ActiveRecord;

class Requirement extends ActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_BLOCKED = 'blocked';

    public static function tableName(): string
    {
        return '{{%requirement}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'code', 'title'], 'required'],
            [['client_id', 'site_id'], 'integer'],
            [['due_date', 'completed_at'], 'safe'],
            [['status', 'category'], 'string', 'max' => 50],
            [['code'], 'string', 'max' => 64],
            [['title'], 'string', 'max' => 255],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getSite()
    {
        return $this->hasOne(Site::class, ['id' => 'site_id']);
    }

    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['requirement_id' => 'id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Новое',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_DONE => 'Выполнено',
            self::STATUS_BLOCKED => 'Заблокировано',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusCss(): string
    {
        return match ($this->status) {
            self::STATUS_DONE => 'badge bg-success',
            self::STATUS_IN_PROGRESS => 'badge bg-warning text-dark',
            self::STATUS_BLOCKED => 'badge bg-secondary',
            default => 'badge bg-danger',
        };
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function isOverdue(): bool
    {
        if ($this->isCompleted() || !$this->due_date) {
            return false;
        }

        return strtotime($this->due_date) < strtotime('today');
    }
}
