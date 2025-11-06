<?php
use yii\helpers\Html;
/* @var $risks app\models\Risk[] */
?>
<div class="row g-3">
    <?php foreach ($risks as $risk): ?>
        <div class="col-md-6">
            <div class="card h-100 border-<?= $risk->severity === 'high' ? 'danger' : ($risk->severity === 'medium' ? 'warning' : 'success') ?>">
                <div class="card-body">
                    <h5 class="card-title mb-1"><?= Html::encode($risk->title) ?></h5>
                    <p class="text-muted mb-2">Серьёзность: <span class="badge bg-<?= $risk->severity === 'high' ? 'danger' : ($risk->severity === 'medium' ? 'warning text-dark' : 'success') ?> badge-status"><?= Html::encode($risk->severity) ?></span></p>
                    <p class="mb-2">Статус: <span class="badge bg-secondary badge-status"><?= Html::encode($risk->status) ?></span></p>
                    <p class="mb-1">Потенциальный штраф: <?= Yii::$app->formatter->asCurrency($risk->loss_min, 'RUB') ?> – <?= Yii::$app->formatter->asCurrency($risk->loss_max, 'RUB') ?></p>
                    <p class="mb-0 text-muted">Влияние: <?= Html::encode($risk->description) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
