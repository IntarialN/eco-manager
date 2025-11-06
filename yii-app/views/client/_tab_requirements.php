<?php
use yii\helpers\Html;
/* @var $requirements app\models\Requirement[] */
/* @var $client app\models\Client */
?>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Код</th>
                <th>Название</th>
                <th>Статус</th>
                <th>Срок</th>
                <th>Площадка</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requirements as $item): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?= Html::encode($item->code) ?></span></td>
                    <td><?= Html::encode($item->title) ?></td>
                    <td>
                        <span class="badge bg-<?= $item->status === 'done' ? 'success' : ($item->status === 'in_progress' ? 'warning text-dark' : 'danger') ?> badge-status">
                            <?= Html::encode($item->status) ?>
                        </span>
                    </td>
                    <td><?= $item->due_date ? Yii::$app->formatter->asDate($item->due_date) : '—' ?></td>
                    <td><?= $item->site ? Html::encode($item->site->name) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
