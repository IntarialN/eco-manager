<?php

declare(strict_types=1);

namespace tests\unit;

use app\models\CalendarEvent;
use app\models\Requirement;
use app\models\RequirementHistory;
use app\models\Risk;
use Yii;

final class RequirementStatusUpdateTest extends RequirementControllerTestCase
{
    public function testUpdateToDoneClosesRiskAndCompletesEvents(): void
    {
        $this->performStatusUpdate(Requirement::STATUS_DONE, 'Done');

        $requirement = Requirement::findOne(1);
        self::assertInstanceOf(Requirement::class, $requirement);
        self::assertSame(Requirement::STATUS_DONE, $requirement->status);
        self::assertNotNull($requirement->completed_at);

        $risk = Risk::find()->where(['requirement_id' => 1])->one();
        self::assertInstanceOf(Risk::class, $risk);
        self::assertSame('closed', $risk->status);

        $events = CalendarEvent::find()->where(['requirement_id' => 1])->orderBy('id')->all();
        self::assertCount(2, $events);
        foreach ($events as $event) {
            self::assertSame(CalendarEvent::STATUS_DONE, $event->status);
            self::assertNotNull($event->completed_at);
        }

        $history = RequirementHistory::find()->where(['requirement_id' => 1])->all();
        self::assertCount(1, $history);
        self::assertSame(Requirement::STATUS_NEW, $history[0]->old_status);
        self::assertSame(Requirement::STATUS_DONE, $history[0]->new_status);
        self::assertSame('Done', $history[0]->comment);
    }

    public function testUpdateToInProgressRestoresScheduling(): void
    {
        // First set requirement to done to ensure we can transition back to in_progress.
        $this->performStatusUpdate(Requirement::STATUS_DONE, 'Closed');

        // Now move to in_progress and verify calendar/risk recalculation.
        $this->performStatusUpdate(Requirement::STATUS_IN_PROGRESS, 'Back to work');

        $requirement = Requirement::findOne(1);
        self::assertInstanceOf(Requirement::class, $requirement);
        self::assertSame(Requirement::STATUS_IN_PROGRESS, $requirement->status);
        self::assertNull($requirement->completed_at);

        $risk = Risk::find()->where(['requirement_id' => 1])->one();
        self::assertInstanceOf(Risk::class, $risk);
        self::assertSame('mitigation', $risk->status);

        $events = CalendarEvent::find()->where(['requirement_id' => 1])->orderBy('id')->all();
        self::assertCount(2, $events);

        $overdueEvent = $events[0];
        $scheduledEvent = $events[1];

        self::assertSame(CalendarEvent::STATUS_OVERDUE, $overdueEvent->status);
        self::assertNull($overdueEvent->completed_at);
        self::assertSame(CalendarEvent::STATUS_SCHEDULED, $scheduledEvent->status);
        self::assertNull($scheduledEvent->completed_at);

        $history = RequirementHistory::find()
            ->where(['requirement_id' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        self::assertCount(2, $history);
        self::assertSame(Requirement::STATUS_NEW, $history[0]->old_status);
        self::assertSame(Requirement::STATUS_DONE, $history[0]->new_status);
        self::assertSame(Requirement::STATUS_DONE, $history[1]->old_status);
        self::assertSame(Requirement::STATUS_IN_PROGRESS, $history[1]->new_status);
    }

    private function performStatusUpdate(string $status, string $comment): void
    {
        $_POST = [
            'id' => 1,
            'status' => $status,
            'comment' => $comment,
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams($_POST);

        $controller = $this->createController();
        $controller->runAction('update-status');
    }
}
