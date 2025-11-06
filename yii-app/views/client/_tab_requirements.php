<?php

use app\models\Requirement;
use app\models\RequirementHistory;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $requirements Requirement[] */
/* @var $client app\models\Client */
/* @var $stats array */
/* @var $statusFilter string */

$stats = array_merge([
    'all' => count($requirements),
    Requirement::STATUS_NEW => 0,
    Requirement::STATUS_IN_PROGRESS => 0,
    Requirement::STATUS_DONE => 0,
    Requirement::STATUS_BLOCKED => 0,
    'overdue' => 0,
    'completedPercent' => 0,
], $stats ?? []);

$filters = [
    'all' => ['label' => 'Все', 'badge' => 'secondary'],
    Requirement::STATUS_NEW => ['label' => Requirement::statusLabels()[Requirement::STATUS_NEW], 'badge' => 'info'],
    Requirement::STATUS_IN_PROGRESS => ['label' => Requirement::statusLabels()[Requirement::STATUS_IN_PROGRESS], 'badge' => 'warning text-dark'],
    'overdue' => ['label' => 'Просрочено', 'badge' => 'danger'],
    Requirement::STATUS_DONE => ['label' => Requirement::statusLabels()[Requirement::STATUS_DONE], 'badge' => 'success'],
];

$statusFilter = $statusFilter ?? 'all';
?>

<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="h5 fw-semibold mb-1">Требования клиента</h2>
        <p class="text-muted mb-0">Выполнено <?= $stats['completedPercent'] ?>% (<?= $stats[Requirement::STATUS_DONE] ?>/<?= max(1, $stats['all']) ?>)</p>
    </div>
    <div class="progress w-100 w-lg-25" style="max-width: 260px;">
        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $stats['completedPercent'] ?>%;" aria-valuenow="<?= $stats['completedPercent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <?php foreach ($filters as $key => $filter): ?>
        <?php
        $isActive = $statusFilter === $key;
        $url = Url::to(['client/view', 'id' => $client->id, 'reqStatus' => $key]);
        $badgeClass = $isActive ? 'badge rounded-pill bg-primary' : 'badge rounded-pill bg-' . $filter['badge'];
        ?>
        <a href="<?= Html::encode($url) ?>" class="<?= $badgeClass ?> text-decoration-none px-3 py-2">
            <?= Html::encode($filter['label']) ?> · <?= $stats[$key] ?? 0 ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Код</th>
                <th>Название</th>
                <th>Статус</th>
                <th>Срок</th>
                <th>Площадка</th>
                <th>Документы</th>
                <th class="text-end">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($requirements)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Нет требований для отображения.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($requirements as $item): ?>
                <tr class="<?= $item->isOverdue() ? 'table-danger' : '' ?>">
                    <td><span class="badge bg-secondary"><?= Html::encode($item->code) ?></span></td>
                    <td>
                        <div class="fw-semibold"><?= Html::encode($item->title) ?></div>
                        <?php if ($item->category): ?>
                            <div class="text-muted small"><?= Html::encode($item->getCategoryLabel()) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="<?= Html::encode($item->getStatusCss()) ?> badge-status">
                            <?= Html::encode($item->getStatusLabel()) ?>
                        </span>
                        <?php if ($client->risks): ?>
                            <?php
                            $linkedRisk = null;
                            foreach ($client->risks as $risk) {
                                if ((int)$risk->requirement_id === (int)$item->id) {
                                    $linkedRisk = $risk;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($linkedRisk && $linkedRisk->status !== 'closed'): ?>
                                <span class="badge bg-danger-subtle text-danger ms-2">Есть риск</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item->due_date): ?>
                            <span class="<?= $item->isOverdue() ? 'text-danger fw-semibold' : '' ?>">
                                <?= Yii::$app->formatter->asDate($item->due_date) ?>
                            </span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= $item->site ? Html::encode($item->site->name) : '—' ?></td>
                    <td><?= count($item->documents) ?></td>
                    <td class="text-end">
                        <?= Html::a('Перейти', ['requirement/view', 'id' => $item->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                    </td>
                </tr>
                <?php if ($item->history): ?>
                    <tr>
                        <td colspan="7" class="border-0 p-0">
                            <div class="collapse border-top bg-light" id="history-<?= $item->id ?>">
                                <div class="p-3 small text-muted">
                                    <?php foreach ($item->history as $history): ?>
                                        <div class="mb-2">
                                            <strong><?= Yii::$app->formatter->asDatetime($history->created_at, 'php:d.m.Y H:i') ?></strong> —
                                            <?= Html::encode(Requirement::statusLabels()[$history->old_status] ?? $history->old_status ?? '—') ?>
                                            →
                                            <?= Html::encode(Requirement::statusLabels()[$history->new_status] ?? $history->new_status) ?>
                                            <?php if ($history->user): ?>
                                                (<?= Html::encode($history->user->username) ?>)
                                            <?php endif; ?>
                                            <?php if ($history->comment): ?>
                                                <div class="text-muted fst-italic">Комментарий: <?= Html::encode($history->comment) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
