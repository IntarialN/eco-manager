<?php

namespace app\models\forms;

use app\models\Risk;
use app\models\RiskActionPlan;
use Yii;
use yii\base\Model;

class RiskActionPlanForm extends Model
{
    public string $task = '';
    public ?int $owner_id = null;
    public ?string $due_date = null;

    public function rules(): array
    {
        return [
            [['task'], 'required'],
            [['task'], 'string', 'max' => 255],
            [['owner_id'], 'integer'],
            [
                'due_date',
                'date',
                'format' => 'php:Y-m-d',
                'message' => 'Срок должен быть в формате ГГГГ-ММ-ДД.',
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'task' => 'Задача',
            'owner_id' => 'Ответственный',
            'due_date' => 'Срок',
        ];
    }

    public function save(Risk $risk): ?RiskActionPlan
    {
        if (!$this->validate()) {
            return null;
        }

        $plan = new RiskActionPlan([
            'risk_id' => $risk->id,
            'task' => $this->task,
            'owner_id' => $this->owner_id ?: null,
            'status' => RiskActionPlan::STATUS_NEW,
            'due_date' => $this->due_date ?: null,
        ]);

        if ($plan->save()) {
            return $plan;
        }

        $this->addErrors($plan->getErrors());
        return null;
    }
}
