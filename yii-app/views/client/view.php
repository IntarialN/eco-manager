<?php
use yii\helpers\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\Tab;

/* @var $this yii\web\View */
/* @var $client app\models\Client */

$this->title = $client->name;
$this->params['breadcrumbs'][] = $this->title;

$requirements = $client->requirements;
$documents = $client->documents;
$events = $client->calendarEvents;
$risks = $client->risks;
$contracts = $client->contracts;
?>

<div class="card shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-start">
        <div>
            <h3 class="card-title mb-1"><?= Html::encode($client->name) ?></h3>
            <p class="mb-1 text-muted">Регистрационный номер: <?= Html::encode($client->registration_number) ?></p>
            <p class="mb-0 text-muted">Категория: <?= Html::encode($client->category) ?></p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary">Объектов: <?= count($client->sites) ?></span>
            <span class="badge bg-success">Требований: <?= count($requirements) ?></span>
        </div>
    </div>
</div>

<?php
$tabs = [
    [
        'label' => 'Карта требований',
        'content' => $this->render('_tab_requirements', [
            'client' => $client,
            'requirements' => $requirements,
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

echo Tab::widget([
    'items' => $tabs,
    'options' => ['class' => 'mb-4'],
]);
?>
