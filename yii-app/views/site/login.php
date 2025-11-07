<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

$this->title = 'Вход в личный кабинет';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-login card shadow-soft p-4">
    <h1 class="h4 fw-semibold mb-3 text-center"><?= Html::encode($this->title) ?></h1>
    <p class="text-muted text-center mb-4">
        Используйте email или логин, выданные администратором. Самостоятельная регистрация временно отключена —
        для получения доступа обратитесь к своей команде или воспользуйтесь демо-учётными данными ниже.
    </p>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'mb-0'],
    ]); ?>

    <?= $form->field($model, 'username')
        ->textInput(['autofocus' => true, 'placeholder' => 'you@example.com или admin'])
        ->label('Email или логин') ?>

    <?= $form->field($model, 'password')
        ->passwordInput(['placeholder' => 'Введите пароль'])
        ->label('Пароль') ?>

    <?= $form->field($model, 'rememberMe')->checkbox()->label('Запомнить меня') ?>

    <div class="d-grid gap-2">
        <?= Html::submitButton('Войти', ['class' => 'btn btn-primary btn-lg', 'name' => 'login-button']) ?>
    </div>

    <p class="text-muted small mt-3 mb-2">
        Демо-учётные данные:
        <br><strong>admin@example.com</strong> (или <strong>admin</strong>) / <strong>Admin#2025</strong> — полный доступ.
        <br><strong>client@example.com</strong> (или <strong>client</strong>) / <strong>Client#2025</strong> — доступ только к своему клиенту.
    </p>
    <p class="text-center mb-0">
        <?= Html::a('Зарегистрировать новый аккаунт клиента', ['site/register'], ['class' => 'small']) ?>
    </p>

    <?php ActiveForm::end(); ?>
</div>
