<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $risks app\models\Risk[] */
?>
<div class="row g-3">
    <?php foreach ($risks as $risk): ?>
        <div class="col-md-6">
            <div class="card h-100 border-<?= $risk->severity === 'high' ? 'danger' : ($risk->severity === 'medium' ? 'warning' : 'success') ?>">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="card-title mb-1"><?= Html::encode($risk->title) ?></h5>
                        <p class="text-muted mb-2">
                            Серьёзность:
                            <span class="badge bg-<?= $risk->severity === 'high' ? 'danger' : ($risk->severity === 'medium' ? 'warning text-dark' : 'success') ?> badge-status">
                                <?= Html::encode($risk->getSeverityLabel()) ?>
                            </span>
                        </p>
                        <p class="mb-2">
                            Статус:
                            <span class="<?= Html::encode($risk->getStatusCss()) ?> badge-status">
                                <?= Html::encode($risk->getStatusLabel()) ?>
                            </span>
                        </p>
                        <p class="mb-1">
                            Потенциальный штраф:
                            <?= $risk->loss_min !== null ? Yii::$app->formatter->asCurrency($risk->loss_min, 'RUB') : '—' ?>
                            –
                            <?= $risk->loss_max !== null ? Yii::$app->formatter->asCurrency($risk->loss_max, 'RUB') : '—' ?>
                        </p>
                        <p class="mb-0 text-muted">Влияние: <?= Html::encode($risk->description) ?></p>
                    </div>
                    <div class="mt-auto text-end">
                        <a class="btn btn-outline-primary btn-sm" href="<?= Html::encode(Url::to(['risk/view', 'id' => $risk->id])) ?>">
                            Перейти
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
