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

                    <?php $form = ActiveForm::begin([
                        'id' => 'client-intake-form',
                        'enableClientValidation' => true,
                    ]); ?>

                    <h2 class="h6 text-uppercase text-muted mt-4 mb-3">1. Данные юрлица и контакты</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'registration_number')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'okved')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'category')->dropDownList($model->getCategoryOptions(), ['prompt' => 'Выберите категорию']) ?>

                    <hr>
                    <h2 class="h6 text-uppercase text-muted mt-4 mb-3">2. Площадка</h2>
                    <?= $form->field($model, 'site_name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'site_address')->textarea(['rows' => 2]) ?>

                    <hr>
                    <h2 class="h6 text-uppercase text-muted mt-4 mb-3">3. Контактные лица</h2>
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

                    <hr>
                    <h2 class="h6 text-uppercase text-muted mt-4 mb-3">4. Экологические параметры</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'hasAirEmissions')->checkbox() ?>
                            <?= $form->field($model, 'hasWasteGeneration')->checkbox() ?>
                            <?= $form->field($model, 'hasWaterUse')->checkbox() ?>
                            <?= $form->field($model, 'emission_sources')->dropDownList($model->getEmissionSourceOptions(), ['prompt' => 'Выберите тип']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'hasSurfaceWaterIntake')->checkbox() ?>
                            <?= $form->field($model, 'needsInstructionDocs')->checkbox() ?>
                            <?= $form->field($model, 'needsTrainingProgram')->checkbox() ?>
                            <?= $form->field($model, 'water_source')->dropDownList($model->getWaterSourceOptions(), ['prompt' => 'Выберите источник']) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'well_license_number')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'well_license_valid_until')->input('date') ?>
                        </div>
                    </div>

                    <hr>
                    <h2 class="h6 text-uppercase text-muted mt-4 mb-3">5. Дополнительная информация</h2>
                    <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

                    <div class="d-flex justify-content-end">
                        <?= Html::submitButton('Создать клиента', ['class' => 'btn btn-primary']) ?>
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
