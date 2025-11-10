<?php

use app\models\ChatSession;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var ChatSession[] $mySessions */
/** @var ChatSession[] $openSessions */
/** @var ChatSession[] $closedSessions */

$this->title = 'Чаты поддержки';
$statuses = ChatSession::statusLabels();
$sources = ChatSession::sourceLabels();
$currentUserId = Yii::$app->user->id;

$renderTable = static function (array $sessions, bool $showAssigned) use ($statuses, $sources, $currentUserId): string {
    if (empty($sessions)) {
        return '<div class="text-center text-muted py-4">Нет записей</div>';
    }

    ob_start();
    ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0 chat-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Клиент / Контакт</th>
                <th>Источник</th>
                <th>Статус</th>
                <?php if ($showAssigned): ?>
                    <th>Назначен</th>
                <?php endif; ?>
                <th>Обновлён</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sessions as $session): ?>
                <?php
                $lastActivity = $session->last_message_at ?? $session->updated_at ?? $session->created_at;
                $hasUnread = $session->assigned_user_id === $currentUserId
                    && ($session->assigned_seen_at === null
                        || ($session->last_message_at && strtotime($session->last_message_at) > strtotime($session->assigned_seen_at)));
                ?>
                <tr class="<?= $hasUnread ? 'chat-row-unread' : '' ?>">
                    <td class="align-middle fw-semibold">
                        #<?= Html::encode($session->id) ?>
                        <?php if ($hasUnread): ?>
                            <span class="chat-unread-dot" title="Есть непрочитанные сообщения"></span>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle">
                        <div class="fw-semibold"><?= Html::encode($session->name ?: 'Без имени') ?></div>
                        <div class="text-muted small"><?= Html::encode($session->external_contact ?: '—') ?></div>
                    </td>
                    <td class="align-middle">
                        <span class="badge text-bg-light">
                            <?= Html::encode($sources[$session->source] ?? $session->source) ?>
                        </span>
                    </td>
                    <td class="align-middle">
                        <span class="badge text-bg-primary">
                            <?= Html::encode($statuses[$session->status] ?? $session->status) ?>
                        </span>
                    </td>
                    <?php if ($showAssigned): ?>
                        <td class="align-middle">
                            <?= $session->assignedUser ? Html::encode($session->assignedUser->username) : '—' ?>
                        </td>
                    <?php endif; ?>
                    <td class="align-middle text-muted">
                        <?= Yii::$app->formatter->asDatetime($lastActivity) ?>
                    </td>
                    <td class="align-middle text-end">
                        <?= Html::a('Открыть', ['chat/thread', 'id' => $session->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
};

$tabs = [
    'my' => [
        'label' => 'Мои',
        'items' => $mySessions,
        'showAssigned' => false,
    ],
    'open' => [
        'label' => 'Открытые',
        'items' => $openSessions,
        'showAssigned' => true,
    ],
    'closed' => [
        'label' => 'Закрытые',
        'items' => $closedSessions,
        'showAssigned' => true,
    ],
];

$activeTab = 'my';
if (empty($mySessions) && !empty($openSessions)) {
    $activeTab = 'open';
} elseif (empty($mySessions) && empty($openSessions) && !empty($closedSessions)) {
    $activeTab = 'closed';
}

?>
<div class="container mt-4 chat-admin">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
    </div>

    <ul class="nav nav-pills mb-3" role="tablist">
        <?php foreach ($tabs as $id => $tab): ?>
            <?php $count = count($tab['items']); ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $id === $activeTab ? 'active' : '' ?>" id="tab-<?= $id ?>-tab"
                        data-bs-toggle="pill" data-bs-target="#tab-<?= $id ?>" type="button" role="tab"
                        aria-controls="tab-<?= $id ?>" aria-selected="<?= $id === $activeTab ? 'true' : 'false' ?>">
                    <?= Html::encode($tab['label']) ?>
                    <span class="badge bg-secondary ms-2"><?= $count ?></span>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="tab-content">
        <?php foreach ($tabs as $id => $tab): ?>
            <div class="tab-pane fade <?= $id === $activeTab ? 'show active' : '' ?>" id="tab-<?= $id ?>" role="tabpanel"
                 aria-labelledby="tab-<?= $id ?>-tab">
                <div class="card shadow-sm">
                    <?= $renderTable($tab['items'], $tab['showAssigned']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
