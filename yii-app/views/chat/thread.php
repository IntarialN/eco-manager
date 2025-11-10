<?php

use app\models\ChatMessage;
use app\models\ChatSession;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var ChatSession $session */
/** @var ChatMessage[] $messages */

$this->title = 'Чат #' . $session->id;
$canAssign = !$session->assigned_user_id || $session->assigned_user_id === Yii::$app->user->id;

?>

<div class="container mt-4 chat-admin">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="<?= Url::to(['chat/inbox']) ?>" class="text-decoration-none small text-muted">&larr; Все чаты</a>
            <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="text-end">
            <div class="fw-semibold"><?= Html::encode($session->name ?: 'Без имени') ?></div>
            <div class="text-muted small"><?= Html::encode($session->external_contact ?: '—') ?></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100 chat-thread">
                <div class="card-body chat-thread__messages">
                    <?php foreach ($messages as $message): ?>
                        <div class="chat-bubble <?= $message->sender_type === \app\models\ChatMessage::SENDER_OPERATOR ? 'chat-bubble--operator' : 'chat-bubble--client' ?>">
                            <div class="chat-bubble__body"><?= nl2br(Html::encode($message->body)) ?></div>
                            <div class="chat-bubble__time">
                                <?= Html::encode($message->sender_type === \app\models\ChatMessage::SENDER_OPERATOR ? 'Оператор' : 'Клиент') ?>
                                &middot;
                                <?= Yii::$app->formatter->asDatetime($message->created_at) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$messages): ?>
                        <div class="text-center text-muted small">Сообщений пока нет.</div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <?php if ($canAssign): ?>
                        <form method="post" action="<?= Url::to(['chat/reply', 'id' => $session->id]) ?>" class="chat-reply-form">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="3" placeholder="Напишите ответ клиенту..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Ответ отправится клиенту и появится в веб-виджете.
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    Отправить
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            Чат закреплён за <?= Html::encode($session->assignedUser->username) ?>. Чтобы ответить, попросите переназначить сессию.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted small">Статус</div>
                            <div class="fw-semibold"><?= Html::encode(ChatSession::statusLabels()[$session->status] ?? $session->status) ?></div>
                        </div>
                        <div>
                            <?php if ($session->assignedUser): ?>
                                <div class="text-muted small">Назначен</div>
                                <div class="fw-semibold"><?= Html::encode($session->assignedUser->username) ?></div>
                            <?php else: ?>
                                <div class="text-muted small">Назначен</div>
                                <div class="text-muted">Не назначен</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!$session->assigned_user_id || $session->assigned_user_id === Yii::$app->user->id): ?>
                        <form method="post" action="<?= Url::to(['chat/assign', 'id' => $session->id]) ?>">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <?= $session->assigned_user_id ? 'Обновить закрепление' : 'Взять в работу' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($session->callbackRequests): ?>
                <div class="card shadow-sm">
                    <div class="card-header">
                        Запросы на звонок
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($session->callbackRequests as $callback): ?>
                            <div class="list-group-item">
                                <div class="fw-semibold"><?= Html::encode($callback->phone) ?></div>
                                <div class="text-muted small">
                                    <?= Yii::$app->formatter->asDatetime($callback->created_at) ?>
                                    <?php if ($callback->preferred_time): ?>
                                        &middot; предпочитаемое время: <?= Yii::$app->formatter->asDatetime($callback->preferred_time) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
