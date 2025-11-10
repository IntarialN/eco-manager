<?php
namespace app\models;

use yii\db\ActiveRecord;

class Contract extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_TERMINATED = 'terminated';

    public static function tableName(): string
    {
        return '{{%contract}}';
    }

    public function rules(): array
    {
        return [
            [['client_id', 'number', 'title', 'status'], 'required'],
            [['client_id'], 'integer'],
            [['signed_at', 'valid_until', 'valid_from'], 'safe'],
            [['amount'], 'number'],
            [['status', 'currency'], 'string', 'max' => 50],
            [['number', 'title', 'client_external_id', 'integration_id', 'integration_revision'], 'string', 'max' => 255],
            [['integration_id'], 'unique'],
            [['client_id'], 'exist', 'targetClass' => Client::class, 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['id' => 'client_id']);
    }

    public function getInvoices()
    {
        return $this->hasMany(Invoice::class, ['contract_id' => 'id']);
    }

    public function getActs()
    {
        return $this->hasMany(Act::class, ['contract_id' => 'id']);
    }
}
