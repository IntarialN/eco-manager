<?php

declare(strict_types=1);

namespace tests\unit;

use app\models\CalendarEvent;
use app\models\Requirement;
use app\models\RequirementHistory;
use app\models\Risk;
use Yii;

final class RequirementStatusUpdateTest extends ControllerTestCase
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
        self::assertCount(3, $events);

        $recurringEvent = $events[0];
        $oneTimeEvent = $events[1];
        $nextOccurrence = $events[2];

        self::assertSame(CalendarEvent::STATUS_DONE, $recurringEvent->status);
        self::assertSame('2000-01-01', $recurringEvent->due_date);
        self::assertNotNull($recurringEvent->completed_at);

        self::assertSame(CalendarEvent::STATUS_DONE, $oneTimeEvent->status);
        self::assertSame('2099-01-01', $oneTimeEvent->due_date);
        self::assertNotNull($oneTimeEvent->completed_at);

        self::assertSame(CalendarEvent::STATUS_SCHEDULED, $nextOccurrence->status);
        self::assertSame('2001-01-01', $nextOccurrence->due_date);
        self::assertNull($nextOccurrence->completed_at);

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
        self::assertCount(3, $events);

        $completedRecurring = $events[0];
        $completedOneTime = $events[1];
        $scheduledNext = $events[2];

        self::assertSame(CalendarEvent::STATUS_DONE, $completedRecurring->status);
        self::assertNotNull($completedRecurring->completed_at);
        self::assertSame(CalendarEvent::STATUS_DONE, $completedOneTime->status);
        self::assertNotNull($completedOneTime->completed_at);
        self::assertSame(CalendarEvent::STATUS_SCHEDULED, $scheduledNext->status);
        self::assertNull($scheduledNext->completed_at);

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

    public function testRecurringEventCreatesSubsequentOccurrences(): void
    {
        $this->performStatusUpdate(Requirement::STATUS_DONE, 'Cycle 1');

        $eventsAfterFirstCycle = CalendarEvent::find()->where(['requirement_id' => 1])->orderBy('id')->all();
        self::assertCount(3, $eventsAfterFirstCycle);
        $nextEvent = $eventsAfterFirstCycle[2];
        self::assertSame('2001-01-01', $nextEvent->due_date);
        self::assertSame(CalendarEvent::STATUS_SCHEDULED, $nextEvent->status);

        $this->performStatusUpdate(Requirement::STATUS_IN_PROGRESS, 'Reopen');
        $this->performStatusUpdate(Requirement::STATUS_DONE, 'Cycle 2');

        $eventsAfterSecondCycle = CalendarEvent::find()->where(['requirement_id' => 1])->orderBy('id')->all();
        self::assertCount(4, $eventsAfterSecondCycle);
        $latest = end($eventsAfterSecondCycle);
        self::assertInstanceOf(CalendarEvent::class, $latest);
        self::assertSame('2002-01-01', $latest->due_date);
        self::assertSame(CalendarEvent::STATUS_SCHEDULED, $latest->status);
    }

    private function performStatusUpdate(string $status, string $comment): void
    {
        $post = [
            'id' => 1,
            'status' => $status,
            'comment' => $comment,
        ];
        $this->runControllerAction('requirement', 'update-status', [], $post);
    }
}
