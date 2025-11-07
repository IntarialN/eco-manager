<?php

use app\models\Risk;
use app\models\RiskActionPlan;
use app\models\forms\RiskActionPlanForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Risk $risk */
/** @var RiskActionPlanForm $planForm */
/** @var array $users */

$this->title = 'Риск: ' . $risk->title;
$this->params['breadcrumbs'][] = ['label' => 'Личный кабинет клиента', 'url' => ['client/view', 'id' => $risk->client_id]];
$this->params['breadcrumbs'][] = $this->title;

$flashes = Yii::$app->session->getAllFlashes();
?>

<?php if (!empty($flashes)): ?>
    <div class="mb-3">
        <?php foreach ($flashes as $type => $message): ?>
            <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= Html::encode(is_array($message) ? implode(' ', $message) : $message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-soft h-100">
            <div class="card-body">
                <h1 class="h4 fw-semibold mb-3"><?= Html::encode($risk->title) ?></h1>
                <p class="mb-2 text-muted"><?= Html::encode($risk->description ?? 'Описание отсутствует') ?></p>

                <dl class="row mb-0">
                    <dt class="col-sm-5">Статус</dt>
                    <dd class="col-sm-7">
                        <span class="<?= Html::encode($risk->getStatusCss()) ?> badge-status">
                            <?= Html::encode($risk->getStatusLabel()) ?>
                        </span>
                    </dd>

                    <dt class="col-sm-5">Серьёзность</dt>
                    <dd class="col-sm-7"><?= Html::encode($risk->getSeverityLabel()) ?></dd>

                    <dt class="col-sm-5">Потенциальный штраф</dt>
                    <dd class="col-sm-7">
                        <?php if ($risk->loss_min !== null || $risk->loss_max !== null): ?>
                            <?= Yii::$app->formatter->asCurrency($risk->loss_min ?? 0, 'RUB') ?>
                            –
                            <?= Yii::$app->formatter->asCurrency($risk->loss_max ?? $risk->loss_min ?? 0, 'RUB') ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-5">Связанное требование</dt>
                    <dd class="col-sm-7">
                        <?php if ($risk->requirement): ?>
                            <a href="<?= Html::encode(Url::to(['requirement/view', 'id' => $risk->requirement->id])) ?>">
                                <?= Html::encode($risk->requirement->title) ?>
                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-5">Обнаружен</dt>
                    <dd class="col-sm-7">
                        <?= $risk->detected_at ? Yii::$app->formatter->asDate($risk->detected_at) : '—' ?>
                    </dd>

                    <dt class="col-sm-5">Устранён</dt>
                    <dd class="col-sm-7">
                        <?= $risk->resolved_at ? Yii::$app->formatter->asDate($risk->resolved_at) : '—' ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-soft mb-4">
            <div class="card-body">
                <h2 class="h5 fw-semibold mb-3">План действий</h2>
                <?php if ($risk->actionPlans): ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Задача</th>
                                    <th>Ответственный</th>
                                    <th>Срок</th>
                                    <th>Статус</th>
                                    <th class="text-end">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($risk->actionPlans as $plan): ?>
                                    <tr>
                                        <td><?= Html::encode($plan->task) ?></td>
                                        <td><?= $plan->owner ? Html::encode($plan->owner->username) : '—' ?></td>
                                        <td><?= $plan->due_date ? Yii::$app->formatter->asDate($plan->due_date) : '—' ?></td>
                                        <td><span class="badge bg-light text-dark"><?= Html::encode($plan->getStatusLabel()) ?></span></td>
                                        <td class="text-end">
                                            <?= Html::beginForm(['risk/update-plan-status', 'id' => $plan->id], 'post', ['class' => 'd-inline-flex gap-2 align-items-center']) ?>
                                                <?= Html::dropDownList(
                                                    'status',
                                                    $plan->status,
                                                    RiskActionPlan::statusLabels(),
                                                    ['class' => 'form-select form-select-sm']
                                                ) ?>
                                                <?= Html::submitButton('Обновить', ['class' => 'btn btn-outline-primary btn-sm']) ?>
                                            <?= Html::endForm() ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">План действий ещё не сформирован.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-soft mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Добавить задачу</h2>
                <?= Html::beginForm(['risk/add-plan', 'id' => $risk->id], 'post', ['class' => 'row g-3 align-items-end']) ?>
                    <div class="col-md-6">
                        <?= Html::label($planForm->getAttributeLabel('task'), 'plan-task', ['class' => 'form-label small text-muted']) ?>
                        <?= Html::textInput('RiskActionPlanForm[task]', $planForm->task, ['class' => 'form-control', 'id' => 'plan-task', 'placeholder' => 'Например: Подготовить проект приказа']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= Html::label($planForm->getAttributeLabel('owner_id'), 'plan-owner', ['class' => 'form-label small text-muted']) ?>
                        <?= Html::dropDownList(
                            'RiskActionPlanForm[owner_id]',
                            $planForm->owner_id,
                            ['' => 'Не назначено'] + $users,
                            ['class' => 'form-select', 'id' => 'plan-owner']
                        ) ?>
                    </div>
                    <div class="col-md-3">
                        <?= Html::label($planForm->getAttributeLabel('due_date'), 'plan-due', ['class' => 'form-label small text-muted']) ?>
                        <?= Html::input('date', 'RiskActionPlanForm[due_date]', $planForm->due_date, ['class' => 'form-control', 'id' => 'plan-due']) ?>
                    </div>
                    <div class="col-12 text-end">
                        <?= Html::submitButton('Добавить задачу', ['class' => 'btn btn-outline-primary btn-sm']) ?>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="card shadow-soft">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">История операций</h2>
                <?php if ($risk->logs): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($risk->logs as $log): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= Yii::$app->formatter->asDatetime($log->created_at, 'php:d.m.Y H:i') ?></strong>
                                        <div><?= Html::encode($log->notes ?? $log->action) ?></div>
                                    </div>
                                    <div class="text-muted small">
                                        <?= $log->user ? Html::encode($log->user->username) : 'Система' ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">История ещё не сформирована.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
