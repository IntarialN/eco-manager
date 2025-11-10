<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Client[] $clients */

$this->title = 'Выбор клиента';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="client-select">
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-2"><?= Html::encode($this->title) ?></h1>
        <p class="text-muted mb-3">Выберите клиента, данные которого хотите открыть. Если нужно, воспользуйтесь поиском по названию или ИНН.</p>
        <?php if ($canSearch ?? true): ?>
            <form id="client-select-form" class="row gy-2 gx-2 align-items-end" method="get">
                <div class="col-sm-6 col-md-5">
                    <label class="form-label small text-muted mb-1" for="client-search">Поиск клиента</label>
                    <input type="text" name="q" id="client-search" value="<?= Html::encode($query ?? '') ?>" class="form-control" placeholder="Например: Зеленый Паттерн / 7700...">
                </div>
                <?php if ($showScopeFilter ?? false): ?>
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label small text-muted mb-1" for="client-scope">Фильтр</label>
                        <select name="scope" id="client-scope" class="form-select">
                            <option value="all" <?= ($scope ?? 'all') === 'all' ? 'selected' : '' ?>>Все клиенты</option>
                            <option value="assigned" <?= ($scope ?? 'all') === 'assigned' ? 'selected' : '' ?>>Назначенные мне</option>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-sm-3 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Найти</button>
                </div>
                <div class="col-sm-3 col-md-2">
                    <a class="btn btn-outline-secondary w-100" href="<?= Html::encode(Url::to(['client/select'])) ?>">Сбросить</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

<?php if ($clients): ?>
        <div class="row g-3">
            <?php foreach ($clients as $client): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-soft-primary text-primary mb-2">Клиент</span>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="client-avatar" style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;color:#fff;background:#<?= substr(md5((string)$client->id), 0, 6) ?>;">
                                    <?= Html::encode(mb_strtoupper(mb_substr($client->name ?? 'К', 0, 1))) ?>
                                </div>
                                <h2 class="h5 mb-0"><?= Html::encode($client->name) ?></h2>
                            </div>
                            <p class="text-muted small mb-1">Категория: <span class="fw-semibold"><?= Html::encode($client->category ?? '—') ?></span></p>
                            <p class="text-muted small mb-1">ИНН: <?= Html::encode($client->registration_number ?? '—') ?></p>
                            <?php if ($client->description): ?>
                                <p class="text-muted small mb-3"><?= Html::encode(mb_substr($client->description, 0, 90)) ?><?= mb_strlen($client->description) > 90 ? '…' : '' ?></p>
                            <?php else: ?>
                                <p class="text-muted small mb-3">Описание не заполнено.</p>
                            <?php endif; ?>
                            <div class="mt-auto">
                                <a class="btn btn-outline-primary btn-sm" href="<?= Html::encode(Url::to(['client/view', 'id' => $client->id])) ?>">
                                    Перейти в кабинет
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
    <?php if (!empty($query)): ?>
                По запросу «<?= Html::encode($query) ?>» клиентов не найдено.
            <?php else: ?>
                Клиенты не назначены. Свяжитесь с администратором, чтобы получить доступ.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$this->registerJs(<<<JS
(function() {
    const form = document.getElementById('client-select-form');
    if (!form) return;
    const searchInput = form.querySelector('input[name="q"]');
    const scopeSelect = form.querySelector('select[name="scope"]');
    if (searchInput) {
        searchInput.focus();
        if (typeof searchInput.selectionStart === 'number') {
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
    }
    const submitForm = () => {
        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            form.submit();
        }
    };
    let debounceTimer = null;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(submitForm, 400);
        });
    }
    if (scopeSelect) {
        scopeSelect.addEventListener('change', submitForm);
    }
})();
JS);
