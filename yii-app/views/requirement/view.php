<?php

use app\models\Requirement;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Requirement $requirement */
/** @var bool $canManage */
/** @var string $redirect */
/** @var array $historyItems */
/** @var array $historyPagination */
/** @var array $historyStatuses */

$this->title = 'Требование: ' . $requirement->title;
$this->params['breadcrumbs'][] = ['label' => 'Клиент ' . $requirement->client->name, 'url' => ['client/view', 'id' => $requirement->client_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-soft">
            <div class="card-body">
                <h1 class="h4 fw-semibold mb-3"><?= Html::encode($requirement->title) ?></h1>
                <div class="mb-3 text-muted">
                    <span class="badge bg-secondary me-2"><?= Html::encode($requirement->code) ?></span>
                    <?= $requirement->category ? Html::encode($requirement->category) : 'Категория не указана' ?>
                </div>

                <dl class="row mb-0">
                    <dt class="col-sm-4">Статус</dt>
                    <dd class="col-sm-8">
                        <span class="<?= Html::encode($requirement->getStatusCss()) ?> badge-status">
                            <?= Html::encode($requirement->getStatusLabel()) ?>
                        </span>
                    </dd>

                    <dt class="col-sm-4">Срок</dt>
                    <dd class="col-sm-8">
                        <?php if ($requirement->due_date): ?>
                            <span class="<?= $requirement->isOverdue() ? 'text-danger fw-semibold' : '' ?>">
                                <?= Yii::$app->formatter->asDate($requirement->due_date) ?>
                            </span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Выполнено</dt>
                    <dd class="col-sm-8">
                        <?= $requirement->completed_at ? Yii::$app->formatter->asDate($requirement->completed_at) : '—' ?>
                    </dd>

                    <dt class="col-sm-4">Площадка</dt>
                    <dd class="col-sm-8">
                        <?= $requirement->site ? Html::encode($requirement->site->name) : '—' ?>
                    </dd>
                </dl>

                <?php if ($canManage): ?>
                    <hr>
                    <?= Html::beginForm(['/requirement/update-status'], 'post', ['class' => 'row gy-2 gx-3 align-items-center']) ?>
                        <?= Html::hiddenInput('id', $requirement->id) ?>
                        <?= Html::hiddenInput('redirect', $redirect) ?>
                        <div class="col-md-4">
                            <?= Html::label('Статус', 'status', ['class' => 'form-label']) ?>
                            <?= Html::dropDownList(
                                'status',
                                $requirement->status,
                                Requirement::statusLabels(),
                                ['class' => 'form-select']
                            ) ?>
                        </div>
                        <div class="col-md-5">
                            <?= Html::label('Комментарий', 'comment', ['class' => 'form-label']) ?>
                            <?= Html::textInput('comment', '', ['class' => 'form-control', 'placeholder' => 'Например: принято заказчиком']) ?>
                        </div>
                        <div class="col-md-3 mt-4 pt-2 text-md-end">
                            <?= Html::submitButton('Обновить статус', ['class' => 'btn btn-primary']) ?>
                        </div>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        </div>

<div class="card shadow-soft">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Связанные документы</h2>
                <?php if ($requirement->documents): ?>
                    <div class="requirement-documents-grid">
                        <?php foreach ($requirement->documents as $document): ?>
                            <?php
                                $statusClass = 'requirement-document-card--status-' . $document->status;
                                $hintLines = [
                                    'Статус: ' . $document->getStatusLabel(),
                                    'Режим: ' . $document->getReviewModeLabel(),
                                ];
                                if (!empty($document->uploaded_at)) {
                                    $hintLines[] = 'Загружен: ' . Yii::$app->formatter->asDatetime($document->uploaded_at, 'php:d.m.Y H:i');
                                }
                                if ($document->auditor_id) {
                                    $hintLines[] = 'Аудитор: ' . ($document->auditor->username ?? $document->auditor->email ?? '—');
                                }
                                $hintText = implode("\n", $hintLines);
                            ?>
                            <div class="requirement-document-card <?= Html::encode($statusClass) ?>">
                                <div class="requirement-document-head">
                                    <div>
                                        <div class="requirement-document-title">
                                            <span class="badge bg-secondary text-uppercase small"><?= Html::encode($document->type) ?></span>
                                            <span><?= Html::encode($document->title) ?></span>
                                        </div>
                                    </div>
                                    <span
                                        class="document-hint document-hint--status-<?= Html::encode($document->status) ?>"
                                        data-hint="<?= Html::encode($hintText) ?>"
                                    >?</span>
                                </div>
                                <div class="requirement-document-meta small text-muted">
                                    <?php if (!empty($document->uploaded_at)): ?>
                                        <span>Загружен <?= Yii::$app->formatter->asDatetime($document->uploaded_at, 'php:d.m.Y H:i') ?></span>
                                    <?php endif; ?>
                                    <?php if ($document->auditor_id): ?>
                                        <span>Аудитор: <?= Html::encode($document->auditor->username ?? $document->auditor->email ?? '—') ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="requirement-document-actions">
                                    <?php if ($document->path): ?>
                                        <a class="btn btn-outline-secondary btn-sm" href="<?= Html::encode($document->path) ?>" target="_blank">Открыть</a>
                                    <?php endif; ?>
                                    <?php if ($canManage): ?>
                                        <a class="btn btn-outline-success btn-sm" href="<?= Html::encode(Url::to(['requirement/approve-document', 'id' => $document->id])) ?>">Подтвердить</a>
                                        <a class="btn btn-outline-danger btn-sm" href="<?= Html::encode(Url::to(['requirement/reject-document', 'id' => $document->id])) ?>">Отклонить</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Документы не привязаны.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($canManage): ?>
            <div class="card shadow-soft mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Добавить документ</h2>
                    <?= Html::beginForm(['requirement/upload-document', 'id' => $requirement->id], 'post', [
                        'enctype' => 'multipart/form-data',
                        'class' => 'requirement-upload-form',
                    ]) ?>
                        <div class="requirement-upload-shell">
                            <div class="requirement-upload-main">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small text-muted d-flex justify-content-between">
                                            <span>Название документа</span>
                                            <span class="text-muted fw-normal">(до 80 символов)</span>
                                        </label>
                                        <?= Html::textInput('DynamicModel[title]', '', [
                                            'class' => 'form-control form-control-lg',
                                            'placeholder' => 'Например: Акт экологической проверки',
                                            'maxlength' => 80,
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Тип</label>
                                        <?= Html::textInput('DynamicModel[type]', '', [
                                            'class' => 'form-control',
                                            'placeholder' => 'Акт, отчёт, паспорт и т.д.',
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Файл</label>
                                        <?= Html::fileInput('DynamicModel[file]', null, ['class' => 'form-control']) ?>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small text-muted d-block">Режим проверки</label>
                                        <div class="d-flex flex-column gap-2 custom-radio-stack">
                                            <label class="form-check requirement-upload-option">
                                                <?= Html::radio('DynamicModel[review_mode]', true, [
                                                    'value' => \app\models\Document::REVIEW_MODE_AUDIT,
                                                    'class' => 'form-check-input',
                                                ]) ?>
                                                <span class="form-check-label">
                                                    С аудитом специалиста
                                                    <small class="text-muted d-block">Мы проверим документ и подтвердим его соответствие (услуга платная, оформляется отдельным счётом).</small>
                                                </span>
                                            </label>
                                            <label class="form-check requirement-upload-option">
                                                <?= Html::radio('DynamicModel[review_mode]', false, [
                                                    'value' => \app\models\Document::REVIEW_MODE_STORAGE,
                                                    'class' => 'form-check-input',
                                                ]) ?>
                                                <span class="form-check-label">
                                                    Без аудита (самостоятельно)
                                                    <small class="text-muted d-block">Документ будет сохранён в системе без проверки.</small>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <?= Html::submitButton('Загрузить документ', ['class' => 'btn btn-primary px-4']) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="requirement-upload-aside">
                                <div class="requirement-upload-aside__inner">
                                    <h6 class="text-uppercase small fw-semibold text-muted mb-2">Подсказки</h6>
                                    <ul class="list-unstyled small text-muted mb-3">
                                        <li class="mb-1">• Поддерживаем форматы PDF, DOCX, XLSX и ZIP до 25&nbsp;МБ.</li>
                                        <li class="mb-1">• Для аудита приложите подписанные версии и сопутствующие письма.</li>
                                        <li class="mb-1">• Указывайте период и площадку в названии файла.</li>
                                    </ul>
                                    <div class="alert alert-info py-2 px-3 mb-0">
                                        <strong>Несколько файлов?</strong><br>
                                        Загрузите их по очереди или объедините в ZIP — система привяжет все версии к требованию.
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?= Html::endForm() ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card shadow-soft">
            <div class="card-body">
                <div class="d-flex flex-column gap-3 mb-3">
                    <h2 class="h5 fw-semibold mb-0">История изменений</h2>
                    <?= Html::beginForm(['requirement/view', 'id' => $requirement->id], 'get', ['class' => 'history-filters row g-2 align-items-end']) ?>
                        <div class="col-md-3">
                            <?= Html::label('Дата', 'historyDate', ['class' => 'form-label small text-muted']) ?>
                            <?= Html::input('date', 'historyDate', $historyPagination['date'] ?? '', [
                                'class' => 'form-control form-control-sm',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= Html::label('Статус', 'historyStatus', ['class' => 'form-label small text-muted']) ?>
                            <?= Html::dropDownList(
                                'historyStatus',
                                $historyPagination['status'] ?? '',
                                ['' => 'Все статусы'] + $historyStatuses,
                                ['class' => 'form-select form-select-sm']
                            ) ?>
                        </div>
                        <div class="col-md-4">
                            <?= Html::label('Комментарий', 'historyComment', ['class' => 'form-label small text-muted']) ?>
                            <?= Html::textInput('historyComment', $historyPagination['comment'] ?? '', [
                                'class' => 'form-control form-control-sm',
                                'placeholder' => 'Текст комментария',
                            ]) ?>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <?= Html::hiddenInput('historyPage', 1) ?>
                            <?= Html::submitButton('Применить', ['class' => 'btn btn-primary btn-sm flex-fill']) ?>
                            <a class="btn btn-outline-secondary btn-sm flex-fill" href="<?= Html::encode(Url::to(['requirement/view', 'id' => $requirement->id])) ?>">Сбросить</a>
                        </div>
                    <?= Html::endForm() ?>
                </div>
                <?php if (!empty($historyItems)): ?>
                    <div class="list-group list-group-flush history-list">
                        <?php foreach ($historyItems as $history): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <div>
                                        <strong><?= Yii::$app->formatter->asDatetime($history->created_at, 'php:d.m.Y H:i') ?></strong>
                                        —
                                        <?= Html::encode(Requirement::statusLabels()[$history->old_status] ?? $history->old_status ?? '—') ?>
                                        →
                                        <?= Html::encode(Requirement::statusLabels()[$history->new_status] ?? $history->new_status) ?>
                                    </div>
                                    <?php if ($history->user): ?>
                                        <span class="text-muted small">
                                            <?= Html::encode($history->user->username ?? $history->user->email ?? '—') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($history->comment): ?>
                                    <div class="text-muted fst-italic small mt-1">Комментарий: <?= Html::encode($history->comment) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (($historyPagination['totalPages'] ?? 1) > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <?php for ($page = 1; $page <= $historyPagination['totalPages']; $page++): ?>
                                    <li class="page-item <?= $page === ($historyPagination['page'] ?? 1) ? 'active' : '' ?>">
                                        <a class="page-link"
                                           href="<?= Html::encode(Url::to([
                                               'requirement/view',
                                               'id' => $requirement->id,
                                               'historyPage' => $page,
                                               'historyDate' => $historyPagination['date'] ?? '',
                                               'historyStatus' => $historyPagination['status'] ?? '',
                                               'historyComment' => $historyPagination['comment'] ?? '',
                                           ])) ?>">
                                            <?= $page ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">История ещё не сформирована.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
