<?php

use app\models\User;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var User[] $users */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card shadow-soft">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1"><?= Html::encode($this->title) ?></h1>
                <p class="text-muted mb-0">Управление доступом к клиентам.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                <tr>
                    <th>Логин</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Основной клиент</th>
                    <th>Назначенные клиенты</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="fw-semibold"><?= Html::encode($user->username) ?></td>
                        <td><?= Html::encode($user->email) ?></td>
                        <td><?= Html::encode($user->getRoleLabel()) ?></td>
                        <td>
                            <?= $user->client ? Html::encode($user->client->name) : '—' ?>
                        </td>
                        <td>
                            <?php
                            $assignedNames = array_map(
                                fn($client) => Html::tag('span', Html::encode($client->name), ['class' => 'badge bg-secondary me-1']),
                                $user->assignedClients
                            );
                            echo $assignedNames ? implode(' ', $assignedNames) : Html::tag('span', 'Нет назначений', ['class' => 'text-muted']);
                            ?>
                        </td>
                        <td class="text-end">
                            <?= Html::a('Изменить', ['update', 'id' => $user->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
