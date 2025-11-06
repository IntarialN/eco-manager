<?php
use yii\helpers\Html;
/* @var $contracts app\models\Contract[] */
?>
<div class="accordion" id="billingAccordion">
    <?php foreach ($contracts as $index => $contract): ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $index ?>">
                <button class="accordion-button <?= $index ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="<?= $index ? 'false' : 'true' ?>" aria-controls="collapse<?= $index ?>">
                    Договор <?= Html::encode($contract->number) ?> — <?= Html::encode($contract->title) ?>
                </button>
            </h2>
            <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index ? '' : 'show' ?>" aria-labelledby="heading<?= $index ?>" data-bs-parent="#billingAccordion">
                <div class="accordion-body">
                    <p class="mb-1">Статус: <span class="badge bg-primary badge-status"><?= Html::encode($contract->status) ?></span></p>
                    <p class="mb-1">Сумма: <?= Yii::$app->formatter->asCurrency($contract->amount, 'RUB') ?></p>
                    <p class="mb-3">Срок действия: <?= $contract->valid_until ? Yii::$app->formatter->asDate($contract->valid_until) : '—' ?></p>

                    <h6>Счета</h6>
                    <ul class="list-group mb-3">
                        <?php foreach ($contract->invoices as $invoice): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    Счёт <?= Html::encode($invoice->number) ?> — <?= Html::encode($invoice->status) ?>
                                </div>
                                <small><?= $invoice->issued_at ? Yii::$app->formatter->asDate($invoice->issued_at) : '—' ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <h6>Акты</h6>
                    <ul class="list-group">
                        <?php foreach ($contract->acts as $act): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    Акт <?= Html::encode($act->number) ?> — <?= Html::encode($act->status) ?>
                                </div>
                                <small><?= $act->issued_at ? Yii::$app->formatter->asDate($act->issued_at) : '—' ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
