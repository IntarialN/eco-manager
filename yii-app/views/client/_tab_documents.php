<?php
use yii\helpers\Html;
/* @var $documents app\models\Document[] */
?>
<div class="row g-3">
    <?php foreach ($documents as $document): ?>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-1"><?= Html::encode($document->title) ?></h5>
                    <p class="text-muted mb-2">Тип: <?= Html::encode($document->type) ?></p>
                    <p class="mb-2">Статус: <span class="badge bg-<?= $document->status === 'approved' ? 'success' : 'warning text-dark' ?> badge-status"><?= Html::encode($document->status) ?></span></p>
                    <p class="mb-0">Загружен: <?= $document->uploaded_at ? Yii::$app->formatter->asDatetime($document->uploaded_at) : '—' ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
