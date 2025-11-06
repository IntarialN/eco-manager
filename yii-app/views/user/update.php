<?php

use app\models\User;
use app\models\forms\UserAssignmentForm;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var User $user */
/** @var UserAssignmentForm $model */

$this->title = 'Назначения для: ' . $user->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card shadow-soft">
    <div class="card-body">
        <h1 class="h4 fw-semibold mb-3"><?= Html::encode($this->title) ?></h1>

        <p class="text-muted">
            Основной клиент (если указан): <?= $user->client ? Html::encode($user->client->name) : '—' ?><br>
            Текущая роль: <?= Html::encode($user->getRoleLabel()) ?>
        </p>

<?php $form = ActiveForm::begin(); ?>

<?= Html::hiddenInput(Html::getInputName($model, 'assignedClientIds'), '') ?>

<div class="assignment-list mb-3">
    <?php foreach ($model->getClientOptions() as $option): ?>
        <?php
        $checked = in_array((int)$option['id'], $model->assignedClientIds, true);
        $inputId = 'assignment-' . $option['id'];
        ?>
        <label class="assignment-list__item" for="<?= Html::encode($inputId) ?>">
            <input
                type="checkbox"
                id="<?= Html::encode($inputId) ?>"
                class="form-check-input me-3"
                name="<?= Html::encode(Html::getInputName($model, 'assignedClientIds')) ?>[]"
                value="<?= Html::encode($option['id']) ?>"
                <?= $checked ? 'checked' : '' ?>
            >
            <div>
                <div class="fw-semibold"><?= Html::encode($option['name']) ?></div>
                <?php if (!empty($option['registration_number'])): ?>
                    <div class="text-muted small">ИНН: <?= Html::encode($option['registration_number']) ?></div>
                <?php endif; ?>
            </div>
        </label>
    <?php endforeach; ?>
</div>

<?php if ($model->hasErrors('assignedClientIds')): ?>
    <div class="text-danger small mb-3"><?= Html::error($model, 'assignedClientIds') ?></div>
<?php endif; ?>

<div class="d-flex gap-2">
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Отменить', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
    </div>
</div>
