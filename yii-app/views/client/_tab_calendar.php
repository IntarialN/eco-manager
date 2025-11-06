<?php
use yii\helpers\Html;
/* @var $events app\models\CalendarEvent[] */
?>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>Событие</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Дата</th>
                <th>Связано с требованием</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= Html::encode($event->title) ?></td>
                    <td><?= Html::encode($event->type) ?></td>
                    <td><span class="badge bg-info text-dark badge-status"><?= Html::encode($event->status) ?></span></td>
                    <td><?= $event->due_date ? Yii::$app->formatter->asDate($event->due_date) : '—' ?></td>
                    <td><?= $event->requirement ? Html::encode($event->requirement->title) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
