<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var app\models\RegisterForm $model */

$this->title = 'Регистрация аккаунта';
$this->params['breadcrumbs'][] = ['label' => 'Вход', 'url' => ['site/login']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-register card shadow-soft p-4">
    <h1 class="h4 fw-semibold mb-3 text-center"><?= Html::encode($this->title) ?></h1>
    <p class="text-muted text-center mb-4">Укажите email и пароль, чтобы получить доступ к личному кабинету. После регистрации заполните анкету клиента.</p>

    <?php $form = ActiveForm::begin([
        'id' => 'register-form',
        'options' => ['class' => 'mb-0'],
    ]); ?>

    <?= $form->field($model, 'email')
        ->textInput(['autofocus' => true, 'placeholder' => 'you@example.com'])
        ->label('Email') ?>

    <?= $form->field($model, 'password')
        ->passwordInput(['placeholder' => 'Придумайте пароль'])
        ->label('Пароль') ?>

    <?= $form->field($model, 'password_repeat')
        ->passwordInput(['placeholder' => 'Повторите пароль'])
        ->label('Повторите пароль') ?>

    <div class="d-grid gap-2">
        <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary btn-lg', 'name' => 'register-button']) ?>
    </div>

    <p class="text-center small mt-3 mb-0">
        Уже есть аккаунт? <?= Html::a('Войти', ['site/login']) ?>
    </p>

    <?php ActiveForm::end(); ?>
</div>
