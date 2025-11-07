<?php

declare(strict_types=1);

namespace tests\unit;

use app\models\Risk;
use app\models\RiskActionPlan;
use app\models\RiskLog;

final class RiskActionPlanControllerTest extends ControllerTestCase
{
    public function testPlanLifecycleChangesRiskStatus(): void
    {
        $post = [
            'RiskActionPlanForm' => [
                'task' => 'Подготовить приказ об устранении',
                'owner_id' => 2,
                'due_date' => '2099-02-01',
            ],
        ];

        $this->runControllerAction('risk', 'add-plan', ['id' => 1], $post);

        $risk = Risk::findOne(1);
        self::assertInstanceOf(Risk::class, $risk);
        self::assertSame(Risk::STATUS_MITIGATION, $risk->status);

        $plan = RiskActionPlan::find()->where(['risk_id' => 1])->one();
        self::assertInstanceOf(RiskActionPlan::class, $plan);
        $logsAfterCreate = RiskLog::find()->where(['risk_id' => 1])->orderBy(['id' => SORT_ASC])->all();
        self::assertCount(2, $logsAfterCreate);
        self::assertSame('plan_task_created', $logsAfterCreate[0]->action);
        self::assertSame('risk_status_changed', $logsAfterCreate[1]->action);
        $notifications = $this->getNotificationStub()->events;
        self::assertCount(2, $notifications);
        self::assertSame('plan_task_created', $notifications[0]['event']);
        self::assertSame('risk_status_changed', $notifications[1]['event']);

        $this->runControllerAction('risk', 'update-plan-status', ['id' => $plan->id], [
            'status' => RiskActionPlan::STATUS_DONE,
        ]);

        $plan->refresh();
        self::assertSame(RiskActionPlan::STATUS_DONE, $plan->status);

        $risk->refresh();
        self::assertSame(Risk::STATUS_CLOSED, $risk->status);
        self::assertNotNull($risk->resolved_at);

        $logs = RiskLog::find()->where(['risk_id' => 1])->orderBy(['id' => SORT_ASC])->all();
        self::assertCount(4, $logs);
        self::assertSame('plan_task_status_updated', $logs[2]->action);
        self::assertSame('risk_status_changed', $logs[3]->action);

        $notifications = $this->getNotificationStub()->events;
        self::assertCount(4, $notifications);
        self::assertSame('plan_task_status_updated', $notifications[2]['event']);
        self::assertSame('risk_status_changed', $notifications[3]['event']);
    }
}
