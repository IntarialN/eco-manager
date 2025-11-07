<?php
namespace app\models;

use yii\db\ActiveRecord;

class Risk extends ActiveRecord
{
    public const STATUS_OPEN = 'open';
    public const STATUS_MITIGATION = 'mitigation';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_ESCALATED = 'escalated';

    public static function tableName(): string
    {
        return '{{%risk}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'title', 'severity'], 'required'],
            [['client_id', 'requirement_id'], 'integer'],
            [['detected_at', 'resolved_at'], 'safe'],
            [['loss_min', 'loss_max'], 'number'],
            [['severity', 'status'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
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

    public function getActionPlans()
    {
        return $this->hasMany(RiskActionPlan::class, ['risk_id' => 'id'])
            ->orderBy(['due_date' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getLogs()
    {
        return $this->hasMany(RiskLog::class, ['risk_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => 'Открыт',
            self::STATUS_MITIGATION => 'В работе',
            self::STATUS_CLOSED => 'Закрыт',
            self::STATUS_ESCALATED => 'Эскалация',
        ];
    }

    public static function severityLabels(): array
    {
        return [
            'low' => 'Низкая',
            'medium' => 'Средняя',
            'high' => 'Высокая',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getSeverityLabel(): string
    {
        return self::severityLabels()[strtolower((string)$this->severity)] ?? $this->severity;
    }

    public function getStatusCss(): string
    {
        return match ($this->status) {
            self::STATUS_CLOSED => 'badge bg-success',
            self::STATUS_MITIGATION => 'badge bg-warning text-dark',
            self::STATUS_ESCALATED => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    public function refreshStatusFromPlans(?int $userId = null, ?string $context = null): ?string
    {
        $plans = $this->getActionPlans()->all();
        if (empty($plans)) {
            return null;
        }

        $oldStatus = $this->status;
        $allDone = true;
        $hasActive = false;

        foreach ($plans as $plan) {
            if ($plan->status !== RiskActionPlan::STATUS_DONE) {
                $allDone = false;
                $hasActive = true;
            }
        }

        $changedStatus = null;
        if ($allDone) {
            if ($this->status !== self::STATUS_CLOSED) {
                $this->status = self::STATUS_CLOSED;
                $this->resolved_at = date('Y-m-d');
                $this->save(false, ['status', 'resolved_at']);
                $this->logStatusChange($oldStatus, $userId, $context);
                $changedStatus = $this->status;
            }
            return $changedStatus;
        }

        $targetStatus = $hasActive ? self::STATUS_MITIGATION : self::STATUS_OPEN;
        if ($this->status !== $targetStatus) {
            $this->status = $targetStatus;
            if ($targetStatus !== self::STATUS_CLOSED) {
                $this->resolved_at = null;
            }
            $this->save(false, ['status', 'resolved_at']);
            $this->logStatusChange($oldStatus, $userId, $context);
            $changedStatus = $this->status;
        }

        return $changedStatus;
    }

    public function addLog(string $action, ?string $notes = null, ?int $userId = null): void
    {
        $log = new RiskLog([
            'risk_id' => $this->id,
            'user_id' => $userId,
            'action' => $action,
            'notes' => $notes,
        ]);
        $log->save(false);
    }

    private function logStatusChange(string $oldStatus, ?int $userId, ?string $context): void
    {
        if ($oldStatus === $this->status) {
            return;
        }

        $message = sprintf(
            'Статус изменён с "%s" на "%s"%s',
            self::statusLabels()[$oldStatus] ?? $oldStatus,
            $this->getStatusLabel(),
            $context ? ' (' . $context . ')' : ''
        );

        $this->addLog('risk_status_changed', $message, $userId);
    }
}
