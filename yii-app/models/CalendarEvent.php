<?php
namespace app\models;

use yii\db\ActiveRecord;

class CalendarEvent extends ActiveRecord
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_DONE = 'done';
    public const STATUS_OVERDUE = 'overdue';

    public const PERIOD_ONCE = 'once';
    public const PERIOD_MONTHLY = 'monthly';
    public const PERIOD_QUARTERLY = 'quarterly';
    public const PERIOD_YEARLY = 'yearly';
    public const PERIOD_CUSTOM = 'custom';

    public static function tableName(): string
    {
        return '{{%calendar_event}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'title', 'due_date'], 'required'],
            [['client_id', 'requirement_id', 'custom_interval_days'], 'integer'],
            [['due_date', 'start_date', 'completed_at', 'created_at'], 'safe'],
            [['periodicity'], 'in', 'range' => array_keys(self::periodicityLabels())],
            [['status', 'type'], 'string', 'max' => 50],
            [['reminder_days'], 'string'],
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

    public function beforeSave($insert): bool
    {
        if ($insert && !$this->periodicity) {
            $this->periodicity = self::PERIOD_ONCE;
        }
        if ($insert && !$this->start_date && $this->due_date) {
            $this->start_date = $this->due_date;
        }

        return parent::beforeSave($insert);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Запланировано',
            self::STATUS_DONE => 'Выполнено',
            self::STATUS_OVERDUE => 'Просрочено',
        ];
    }

    public static function periodicityLabels(): array
    {
        return [
            self::PERIOD_ONCE => 'Однократно',
            self::PERIOD_MONTHLY => 'Ежемесячно',
            self::PERIOD_QUARTERLY => 'Ежеквартально',
            self::PERIOD_YEARLY => 'Ежегодно',
            self::PERIOD_CUSTOM => 'Период по расписанию',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getPeriodicityLabel(): string
    {
        return self::periodicityLabels()[$this->periodicity] ?? $this->periodicity;
    }

    public function isRecurring(): bool
    {
        return $this->periodicity !== self::PERIOD_ONCE;
    }

    public function getNextDueDate(): ?string
    {
        if (!$this->isRecurring()) {
            return null;
        }

        $base = $this->due_date ?? $this->start_date;
        if (!$base) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $base);
        if (!$date) {
            return null;
        }

        return match ($this->periodicity) {
            self::PERIOD_MONTHLY => $date->modify('+1 month')->format('Y-m-d'),
            self::PERIOD_QUARTERLY => $date->modify('+3 months')->format('Y-m-d'),
            self::PERIOD_YEARLY => $date->modify('+1 year')->format('Y-m-d'),
            self::PERIOD_CUSTOM => $this->custom_interval_days
                ? $date->modify('+' . (int)$this->custom_interval_days . ' days')->format('Y-m-d')
                : null,
            default => null,
        };
    }
}
