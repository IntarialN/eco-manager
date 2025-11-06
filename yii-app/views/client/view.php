<?php
use yii\bootstrap5\Tabs;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $client app\models\Client */

$this->title = 'Личный кабинет клиента';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = $client->name;

$allRequirements = $allRequirements ?? $client->requirements;
$displayRequirements = $requirements ?? $allRequirements;
$documents = $client->documents;
$events = $client->calendarEvents;
$risks = $client->risks;
$contracts = $client->contracts;

$activeRequirements = 0;
foreach ($allRequirements as $requirement) {
    if (!$requirement->isCompleted()) {
        $activeRequirements++;
    }
}

$approvedDocuments = 0;
foreach ($documents as $document) {
    if ($document->status === 'approved') {
        $approvedDocuments++;
    }
}

$highRisks = 0;
foreach ($risks as $risk) {
    if ($risk->severity === 'high' && $risk->status !== 'resolved') {
        $highRisks++;
    }
}

$upcomingEvent = null;
$upcomingEventTs = null;
$nowTs = time();
foreach ($events as $event) {
    if (!$event->due_date) {
        continue;
    }
    $eventTs = strtotime($event->due_date);
    if ($eventTs === false) {
        continue;
    }
    if ($eventTs >= $nowTs && ($upcomingEventTs === null || $eventTs < $upcomingEventTs)) {
        $upcomingEvent = $event;
        $upcomingEventTs = $eventTs;
    }
}

$summaryCards = [
    [
        'title' => 'Активные требования',
        'value' => $activeRequirements,
        'description' => 'в работе',
        'icon' => 'bi-list-check',
        'accent' => 'primary',
    ],
    [
        'title' => 'Готовые артефакты',
        'value' => $approvedDocuments,
        'description' => 'подтвержденные документы',
        'icon' => 'bi-archive-check',
        'accent' => 'success',
    ],
    [
        'title' => 'Высокие риски',
        'value' => $highRisks,
        'description' => 'требуют внимания',
        'icon' => 'bi-exclamation-octagon',
        'accent' => 'danger',
    ],
    [
        'title' => 'Ближайшее событие',
        'value' => $upcomingEvent ? Yii::$app->formatter->asDate($upcomingEvent->due_date, 'php:d.m') : '—',
        'description' => $upcomingEvent ? Html::encode($upcomingEvent->title) : 'Нет запланированных задач',
        'icon' => 'bi-calendar-event',
        'accent' => 'warning',
    ],
];

$tabs = [
    [
        'label' => 'Карта требований',
        'content' => $this->render('_tab_requirements', [
            'client' => $client,
            'requirements' => $displayRequirements,
            'stats' => $requirementsStats ?? [],
            'statusFilter' => $requirementStatusFilter ?? 'all',
        ]),
        'active' => true,
    ],
    [
        'label' => 'Артефакты / Хранилище',
        'content' => $this->render('_tab_documents', [
            'documents' => $documents,
        ]),
    ],
    [
        'label' => 'Календарь событий',
        'content' => $this->render('_tab_calendar', [
            'events' => $events,
        ]),
    ],
    [
        'label' => 'Риски',
        'content' => $this->render('_tab_risks', [
            'risks' => $risks,
        ]),
    ],
    [
        'label' => 'Договоры / Счета / Акты',
        'content' => $this->render('_tab_billing', [
            'contracts' => $contracts,
        ]),
    ],
];
?>

<?php
$lastUpdatedTs = null;
foreach ($allRequirements as $requirement) {
    foreach ($requirement->history as $history) {
        $ts = strtotime($history->created_at);
        if ($lastUpdatedTs === null || $ts > $lastUpdatedTs) {
            $lastUpdatedTs = $ts;
        }
    }
}
$lastUpdatedText = $lastUpdatedTs ? Yii::$app->formatter->asDatetime($lastUpdatedTs, 'php:d F Y H:i') : Yii::$app->formatter->asDate('now');
?>

<div class="client-profile card shadow-soft mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <span class="badge rounded-pill bg-soft-info text-info text-uppercase fw-semibold mb-2">Клиент</span>
            <h1 class="card-title mb-2"><?= Html::encode($client->name) ?></h1>
            <p class="mb-1 text-muted">ИНН: <span class="text-dark fw-semibold"><?= Html::encode($client->registration_number) ?></span></p>
            <p class="mb-0 text-muted small">Обновлено: <span class="text-dark"><?= Html::encode($lastUpdatedText) ?></span></p>
        </div>
        <div class="client-stats text-end">
            <div class="mini-stat">
                <span class="mini-stat-label">Объекты</span>
                <span class="mini-stat-value"><?= count($client->sites) ?></span>
            </div>
            <div class="mini-stat">
                <span class="mini-stat-label">Требования</span>
                <span class="mini-stat-value"><?= count($allRequirements) ?></span>
            </div>
            <div class="d-flex gap-2 mt-3 justify-content-end">
                <button class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-cloud-upload me-1"></i>Загрузить CSV</button>
                <button class="btn btn-outline-secondary btn-sm" type="button"><i class="bi bi-plus-circle me-1"></i>Импорт требований</button>
            </div>
        </div>
    </div>
</div>

<div class="filter-panel card shadow-soft mb-4">
    <div class="card-body">
        <form class="row gy-2 gx-3 align-items-center">
            <div class="col-sm-6 col-xl-3">
                <label class="form-label text-muted mb-1">Категория НВОС</label>
                <select class="form-select">
                    <option selected>Все категории</option>
                    <option>I</option>
                    <option>II</option>
                    <option>III</option>
                </select>
            </div>
            <div class="col-sm-6 col-xl-3">
                <label class="form-label text-muted mb-1">Аудит</label>
                <select class="form-select">
                    <option selected>Любой статус</option>
                    <option>Без аудита</option>
                    <option>Требует аудита</option>
                </select>
            </div>
            <div class="col-sm-6 col-xl-3">
                <label class="form-label text-muted mb-1">Период</label>
                <select class="form-select">
                    <option selected>Все периоды</option>
                    <option><?= date('Y') ?></option>
                    <option><?= date('Y') + 1 ?></option>
                </select>
            </div>
            <div class="col-sm-6 col-xl-3">
                <label class="form-label text-muted mb-1">Поиск по требованиям</label>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Введите код или название">
                    <button class="btn btn-primary" type="button"><i class="bi bi-search me-1"></i>Найти</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($summaryCards as $card): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="summary-card summary-card--<?= Html::encode($card['accent']) ?>">
                <div class="summary-icon bg-<?= Html::encode($card['accent']) ?>-subtle text-<?= Html::encode($card['accent']) ?>">
                    <i class="bi <?= Html::encode($card['icon']) ?>"></i>
                </div>
                <div>
                    <div class="summary-value"><?= Html::encode($card['value']) ?></div>
                    <div class="summary-label"><?= Html::encode($card['title']) ?></div>
                    <?php if (!empty($card['description'])): ?>
                        <p class="summary-hint mb-0"><?= Html::encode($card['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="tab-wrapper">
    <?= Tabs::widget([
        'items' => $tabs,
        'navType' => 'nav-pills',
        'options' => ['class' => 'nav nav-pills eco-tabs mb-3'],
        'tabContentOptions' => ['class' => 'tab-content eco-tab-content card shadow-soft'],
        'itemOptions' => ['class' => 'p-4 eco-tab-pane'],
        'encodeLabels' => false,
    ]) ?>
</div>
