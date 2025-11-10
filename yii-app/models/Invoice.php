<?php
namespace app\models;

use yii\db\ActiveRecord;

class Invoice extends ActiveRecord
{
    public const STATUS_ISSUED = 'issued';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName(): string
    {
        return '{{%invoice}}';
    }

    public function rules(): array
    {
        return [
            [['contract_id', 'number', 'status', 'amount'], 'required'],
            [['contract_id'], 'integer'],
            [['issued_at', 'paid_at', 'due_date'], 'safe'],
            [['amount'], 'number'],
            [['status', 'currency'], 'string', 'max' => 50],
            [['number', 'integration_id'], 'string', 'max' => 100],
            [['integration_id'], 'unique'],
            [['contract_id'], 'exist', 'targetClass' => Contract::class, 'targetAttribute' => ['contract_id' => 'id']],
        ];
    }

    public function getContract()
    {
        return $this->hasOne(Contract::class, ['id' => 'contract_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ISSUED => 'Выставлен',
            self::STATUS_PAID => 'Оплачен',
            self::STATUS_OVERDUE => 'Просрочен',
            self::STATUS_CANCELLED => 'Аннулирован',
        ];
    }
}
