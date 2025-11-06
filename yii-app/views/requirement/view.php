<?php

use app\models\Requirement;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Requirement $requirement */
/** @var bool $canManage */
/** @var string $redirect */

$this->title = 'Требование: ' . $requirement->title;
$this->params['breadcrumbs'][] = ['label' => 'Клиент ' . $requirement->client->name, 'url' => ['client/view', 'id' => $requirement->client_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row g-4">
    <div class="col-lg-8">
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
                <h2 class="h5 fw-semibold mb-3">История изменений</h2>
                <?php if ($requirement->history): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($requirement->history as $history): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= Yii::$app->formatter->asDatetime($history->created_at, 'php:d.m.Y H:i') ?></strong>
                                        —
                                        <?= Html::encode(Requirement::statusLabels()[$history->old_status] ?? $history->old_status ?? '—') ?>
                                        →
                                        <?= Html::encode(Requirement::statusLabels()[$history->new_status] ?? $history->new_status) ?>
                                    </div>
                                    <?php if ($history->user): ?>
                                        <span class="text-muted small"><?= Html::encode($history->user->username) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($history->comment): ?>
                                    <div class="text-muted fst-italic small mt-1">Комментарий: <?= Html::encode($history->comment) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">История ещё не сформирована.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-soft">
            <div class="card-body">
        <h2 class="h6 fw-semibold mb-3">Связанные документы</h2>
        <?php if ($canManage): ?>
            <?= Html::beginForm(['requirement/upload-document', 'id' => $requirement->id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'mb-3']) ?>
                <div class="row g-2 align-items-end">
                    <div class="col-12">
                        <label class="form-label small text-muted">Название документа</label>
                        <?= Html::textInput('DynamicModel[title]', '', ['class' => 'form-control', 'placeholder' => 'Например: Акт проверки']) ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">Тип</label>
                        <?= Html::textInput('DynamicModel[type]', '', ['class' => 'form-control', 'placeholder' => 'Тип документа']) ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">Файл</label>
                        <?= Html::fileInput('DynamicModel[file]', null, ['class' => 'form-control']) ?>
                    </div>
                    <div class="col-12 text-end">
                        <?= Html::submitButton('Загрузить', ['class' => 'btn btn-outline-primary btn-sm']) ?>
                    </div>
                </div>
            <?= Html::endForm() ?>
        <?php endif; ?>
        <?php if ($requirement->documents): ?>
            <ul class="list-unstyled mb-0">
                <?php foreach ($requirement->documents as $document): ?>
                    <li class="mb-2">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <span class="badge bg-secondary me-1"><?= Html::encode($document->type) ?></span>
                                <?= Html::encode($document->title) ?>
                                <div class="text-muted small"><?= Html::encode($document->getStatusLabel()) ?></div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($document->path): ?>
                                    <a class="btn btn-outline-secondary btn-sm" href="<?= Html::encode($document->path) ?>" target="_blank">Открыть</a>
                                <?php endif; ?>
                                <?php if ($canManage): ?>
                                    <a class="btn btn-outline-success btn-sm" href="<?= Html::encode(Url::to(['requirement/approve-document', 'id' => $document->id])) ?>">Подтвердить</a>
                                    <a class="btn btn-outline-danger btn-sm" href="<?= Html::encode(Url::to(['requirement/reject-document', 'id' => $document->id])) ?>">Отклонить</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted mb-0">Документы не привязаны.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
