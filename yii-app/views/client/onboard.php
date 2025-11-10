<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\models\forms\ClientIntakeForm;

/** @var yii\web\View $this */
/** @var ClientIntakeForm $model */

$this->title = 'Создать клиента и карту требований';
?>

<div class="client-onboard">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-soft mb-4">
                <div class="card-body">
                    <h1 class="h4 mb-3"><?= Html::encode($this->title) ?></h1>
                    <p class="text-muted">
                        Заполните ключевые атрибуты из анкеты — система автоматически создаст клиента, объект
                        и стартовый набор требований с дедлайнами.
                    </p>
                    <div id="formErrors" class="alert alert-danger d-none"></div>

<?php $form = ActiveForm::begin([
    'id' => 'client-intake-form',
    'enableClientValidation' => true,
    'options' => ['novalidate' => true],
]); ?>

<div class="intake-stepper mb-4">
    <ul class="nav nav-pills step-nav" id="intakeSteps" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="step-company-tab" data-bs-toggle="pill" data-bs-target="#step-company" type="button" role="tab" aria-controls="step-company">
                <span>1</span> Компания
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step-site-tab" data-bs-toggle="pill" data-bs-target="#step-site" type="button" role="tab" aria-controls="step-site">
                <span>2</span> Площадка
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step-contacts-tab" data-bs-toggle="pill" data-bs-target="#step-contacts" type="button" role="tab" aria-controls="step-contacts">
                <span>3</span> Контакты
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step-eco-tab" data-bs-toggle="pill" data-bs-target="#step-eco" type="button" role="tab" aria-controls="step-eco">
                <span>4</span> Экология
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step-training-tab" data-bs-toggle="pill" data-bs-target="#step-training" type="button" role="tab" aria-controls="step-training">
                <span>5</span> Ответственные
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step-notes-tab" data-bs-toggle="pill" data-bs-target="#step-notes" type="button" role="tab" aria-controls="step-notes">
                <span>6</span> Дополнительно
            </button>
        </li>
    </ul>
</div>

<div class="tab-content" id="intakeStepsContent">
    <div class="tab-pane fade show active" id="step-company" role="tabpanel" aria-labelledby="step-company-tab">
        <h2 class="section-title">Данные юрлица</h2>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'registration_number')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'okved')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'category')->dropDownList($model->getCategoryOptions(), ['prompt' => 'Выберите категорию']) ?>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="step-site" role="tabpanel" aria-labelledby="step-site-tab">
        <h2 class="section-title">Площадка и адрес</h2>
        <?= $form->field($model, 'site_name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'site_address')->textarea(['rows' => 2]) ?>
    </div>

    <div class="tab-pane fade" id="step-contacts" role="tabpanel" aria-labelledby="step-contacts-tab">
        <h2 class="section-title">Контакты и доступы</h2>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'contact_name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'contact_email')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'contact_role')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'contact_phone')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <?= $form->field($model, 'access_channels')->textarea(['rows' => 2])->hint('Например: доступ к ПИК, email-адреса для уведомлений.') ?>

        <?php if ($showManagerField ?? false): ?>
            <div class="mb-3">
                <label class="form-label">Менеджер клиента</label>
                <?= Html::hiddenInput($model->formName() . '[manager_id]', $model->manager_id, ['id' => 'manager-id']) ?>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span id="manager-summary" class="<?= $model->manager_id ? '' : 'text-muted' ?>">
                        <?= $model->manager_id ? Html::encode($model->getSelectedManagerLabel()) : 'Не назначен' ?>
                    </span>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#managerModal">
                        Выбрать менеджера
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="manager-clear" <?= $model->manager_id ? '' : 'disabled' ?>>
                        Очистить
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

<div class="tab-pane fade" id="step-eco" role="tabpanel" aria-labelledby="step-eco-tab">
        <h2 class="section-title">Экологические параметры</h2>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'hasAirEmissions')->checkbox() ?>
                <?= $form->field($model, 'hasWasteGeneration')->checkbox() ?>
                <?= $form->field($model, 'hazardous_waste_present')->checkbox() ?>
                <?= $form->field($model, 'livestock_byproducts')->checkbox() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'annual_emissions_tons')->input('number', ['step' => '0.1', 'min' => 0])->hint('Суммарный объём выбросов за год, тонн/год.') ?>
                <?= $form->field($model, 'annual_waste_kg')->input('number', ['step' => '0.1', 'min' => 0])->hint('Суммарное образование отходов за год, кг.') ?>
                <?= $form->field($model, 'hazardous_substances_class')->dropDownList($model->getHazardousClassOptions(), ['prompt' => 'Не выбрано']) ?>
                <?= $form->field($model, 'emission_sources')->dropDownList($model->getEmissionSourceOptions(), ['prompt' => 'Выберите тип']) ?>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <?= $form->field($model, 'hasWaterUse')->checkbox()->hint('Формирует требование на лицензию недропользования.') ?>
                <?= $form->field($model, 'hasSurfaceWaterIntake')->checkbox()->hint('Создаёт требования по водозабору/сбросам.') ?>
                <?= $form->field($model, 'water_source')->dropDownList($model->getWaterSourceOptions(), ['prompt' => 'Выберите источник']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'well_license_number')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'well_license_valid_until')->input('date') ?>
            </div>
        </div>
    </div>

<div class="tab-pane fade" id="step-training" role="tabpanel" aria-labelledby="step-training-tab">
        <h2 class="section-title">Ответственные лица</h2>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'responsible_person_count')->input('number', ['min' => 0, 'max' => 999]) ?>
                <?= $form->field($model, 'responsible_person_trained')->checkbox() ?>
                <?= $form->field($model, 'needsInstructionDocs')->checkbox()->hint('Принудительно включает требование по инструкциям (req_11).') ?>
                <?= $form->field($model, 'needsTrainingProgram')->checkbox() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'training_valid_until')->input('date')->hint('При истекающем сроке создаётся требование на обучение.') ?>
            </div>
        </div>
    </div>

<div class="tab-pane fade" id="step-notes" role="tabpanel" aria-labelledby="step-notes-tab">
        <h2 class="section-title">Дополнительные сведения</h2>
        <?= $form->field($model, 'notes')->textarea(['rows' => 4])->hint('Например: история проверок, планы развития, пожелания к аналитике.') ?>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between step-controls mt-4">
    <button type="button" class="btn btn-outline-secondary" id="stepPrev" disabled>
        Назад
    </button>
    <div class="text-muted small">
        Шаг <span id="stepIndex">1</span> из 6
    </div>
    <button type="button" class="btn btn-primary" id="stepNext">
        Далее
    </button>
</div>

<div class="text-end mt-4">
    <?= Html::submitButton('Создать клиента', ['class' => 'btn btn-success', 'id' => 'submitButton', 'style' => 'display:none']) ?>
</div>

<?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Памятка</h2>
                    <ul class="small text-muted ps-3">
                        <li>Категория НВОС используется для выбора базовых требований.</li>
                        <li>Флаги по водопользованию и отходам влияют на набор дедлайнов.</li>
                        <li>После сохранения вы сможете отредактировать детали на карточке клиента.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Полный шаблон анкеты описан в `docs/client/onboarding-form.md`. При расширении перечня вопросов
                        обязательно обновляйте этот экран.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showManagerField ?? false): ?>
<div class="modal fade" id="managerModal" tabindex="-1" aria-labelledby="managerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="managerModalLabel">Выбор менеджера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="manager-search" placeholder="Поиск по имени или email">
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="manager-table-body">
                            <tr><td colspan="4" class="text-muted text-center py-3">Загрузка...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="text-muted small" id="manager-pagination-info"></div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="manager-prev">Назад</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="manager-next">Вперёд</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerJs(<<<'JS'
(function() {
    const modalEl = document.getElementById('managerModal');
    if (!modalEl) { return; }
    const tableBody = modalEl.querySelector('#manager-table-body');
    const searchInput = modalEl.querySelector('#manager-search');
    const prevBtn = modalEl.querySelector('#manager-prev');
    const nextBtn = modalEl.querySelector('#manager-next');
    const paginationInfo = modalEl.querySelector('#manager-pagination-info');
    const summaryEl = document.getElementById('manager-summary');
    const hiddenInput = document.getElementById('manager-id');
    const clearBtn = document.getElementById('manager-clear');

    let currentPage = 1;
    let totalPages = 1;
    let currentQuery = '';

    function updatePaginationInfo(total, page) {
        paginationInfo.textContent = `Страница ${page} из ${totalPages}, всего ${total} менеджеров`;
        prevBtn.disabled = page <= 1;
        nextBtn.disabled = page >= totalPages;
    }

    async function loadManagers(page = 1) {
        currentPage = page;
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Загрузка...</td></tr>';
        try {
            const response = await fetch(`/client/manager-list?page=${page}&q=${encodeURIComponent(currentQuery)}`);
            const data = await response.json();
            totalPages = data.pagination.totalPages;
            updatePaginationInfo(data.pagination.total, data.pagination.page);

            if (!data.items.length) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Ничего не найдено.</td></tr>';
                return;
            }

            tableBody.innerHTML = data.items.map(item => `
                <tr>
                    <td>${item.label}</td>
                    <td>${item.email}</td>
                    <td>${item.role}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-primary" data-select-manager="1" data-id="${item.id}" data-label="${item.label} (${item.email})">
                            Выбрать
                        </button>
                    </td>
                </tr>
            `).join('');
        } catch (e) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Ошибка загрузки.</td></tr>';
        }
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        currentQuery = '';
        searchInput.value = '';
        loadManagers(1);
        searchInput.focus();
    });

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            loadManagers(currentPage - 1);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentPage < totalPages) {
            loadManagers(currentPage + 1);
        }
    });

    searchInput.addEventListener('input', () => {
        currentQuery = searchInput.value.trim();
        loadManagers(1);
    });

    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-select-manager]');
        if (!button) {
            return;
        }
        const id = button.getAttribute('data-id');
        const label = button.getAttribute('data-label');
        hiddenInput.value = id;
        summaryEl.textContent = label;
        summaryEl.classList.remove('text-muted');
        if (clearBtn) {
            clearBtn.disabled = false;
        }
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            hiddenInput.value = '';
            summaryEl.textContent = 'Не назначен';
            summaryEl.classList.add('text-muted');
            clearBtn.disabled = true;
        });
    }
})();
JS);
?>
<?php endif; ?>

<?php
$this->registerJs(<<<'JS'
(function() {
    const stepButtons = Array.from(document.querySelectorAll('#intakeSteps button[data-bs-toggle="pill"]'));
    const prevBtn = document.getElementById('stepPrev');
    const nextBtn = document.getElementById('stepNext');
    const stepIndexEl = document.getElementById('stepIndex');
    const submitBtn = document.getElementById('submitButton');
    const form = document.getElementById('client-intake-form');
    const errorAlert = document.getElementById('formErrors');

    if (!stepButtons.length || !window.bootstrap) {
        if (submitBtn) {
            submitBtn.style.display = '';
        }
        if (nextBtn) {
            nextBtn.style.display = 'none';
        }
        return;
    }

    let currentIndex = stepButtons.findIndex((btn) => btn.classList.contains('active'));
    if (currentIndex < 0) {
        currentIndex = 0;
    }
    const total = stepButtons.length;
    const stepButtonMap = new Map(stepButtons.map((btn) => {
        const target = btn.getAttribute('data-bs-target');
        return [target, btn];
    }));

    const updateControls = (index) => {
        if (!stepIndexEl) {
            return;
        }
        stepIndexEl.textContent = index + 1;
        if (prevBtn) {
            prevBtn.disabled = index === 0;
        }
        const isLast = index === total - 1;
        if (nextBtn) {
            nextBtn.style.display = isLast ? 'none' : '';
        }
        if (submitBtn) {
            submitBtn.style.display = isLast ? '' : 'none';
        }
    };

    const goTo = (index) => {
        if (index < 0 || index >= total) {
            return;
        }
        const target = stepButtons[index];
        const tab = bootstrap.Tab.getOrCreateInstance(target);
        tab.show();
    };

    stepButtons.forEach((button, idx) => {
        button.addEventListener('shown.bs.tab', () => {
            currentIndex = idx;
            updateControls(idx);
        });
    });

    if (prevBtn) {
        prevBtn.addEventListener('click', () => goTo(currentIndex - 1));
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => goTo(currentIndex + 1));
    }

    const clearStepErrors = () => {
        stepButtons.forEach((btn) => btn.classList.remove('has-error'));
        if (errorAlert) {
            errorAlert.classList.add('d-none');
            errorAlert.innerHTML = '';
        }
    };

    const markStepError = (paneId) => {
        const key = `#${paneId}`;
        const btn = stepButtonMap.get(key);
        if (btn) {
            btn.classList.add('has-error');
        }
    };

    const showErrorSummary = (stepTitles) => {
        if (!errorAlert) {
            return;
        }
        const listItems = stepTitles.map((title) => `<li>${title}</li>`).join('');
        errorAlert.innerHTML = `<strong>Проверьте обязательные поля:</strong><ul class="mb-0">${listItems}</ul>`;
        errorAlert.classList.remove('d-none');
    };

    const scanExistingErrors = () => {
        if (!form) { return; }
        const invalidElements = form.querySelectorAll('.is-invalid');
        const seen = new Set();
        invalidElements.forEach((el) => {
            const pane = el.closest('.tab-pane');
            if (pane && !seen.has(pane.id)) {
                seen.add(pane.id);
                markStepError(pane.id);
            }
        });
        if (invalidElements.length && errorAlert) {
            const titles = Array.from(seen).map((paneId) => {
                const btn = stepButtonMap.get(`#${paneId}`);
                return btn ? btn.innerText.trim() : paneId;
            });
            errorAlert.classList.remove('d-none');
            errorAlert.innerHTML = `<strong>Проверьте обязательные поля:</strong><ul class="mb-0">${titles.map((title) => `<li>${title}</li>`).join('')}</ul>`;
        }
    };

    if (form) {
        form.addEventListener('submit', (event) => {
            clearStepErrors();
            const invalidSteps = new Map();
            const fields = form.querySelectorAll('input, select, textarea');
            let firstInvalidField = null;

            fields.forEach((field) => {
                if (field.disabled || field.type === 'hidden') {
                    return;
                }
                if (!field.closest('.tab-pane')) {
                    return;
                }
                if (field.checkValidity()) {
                    return;
                }
                const pane = field.closest('.tab-pane');
                if (!pane) {
                    return;
                }
                const button = stepButtonMap.get(`#${pane.id}`);
                if (button) {
                    button.classList.add('has-error');
                    invalidSteps.set(pane.id, button.innerText.trim());
                }
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
            });

            if (invalidSteps.size > 0) {
                event.preventDefault();
                showErrorSummary(Array.from(invalidSteps.values()));
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.reportValidity();
                }
                const firstStepId = invalidSteps.keys().next().value;
                const firstIndex = stepButtons.findIndex((btn) => btn.getAttribute('data-bs-target') === `#${firstStepId}`);
                if (firstIndex >= 0 && firstIndex !== currentIndex) {
                    goTo(firstIndex);
                }
            } else {
                clearStepErrors();
            }
        });
    }

    updateControls(currentIndex);
    scanExistingErrors();
})();
JS);
?>
