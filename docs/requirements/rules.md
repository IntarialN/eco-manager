# Правила автоназначения требований

Цель документа — описать алгоритм формирования карты требований в личном кабинете на основании анкеты клиента. Базовые данные берём из источника «Критерии для формирования требований» и анкеты (см. `docs/client/onboarding-form.md`, добавить после подготовки).

## 1. Входные атрибуты анкеты

- `emission_category` — категория объекта по НВОС (I, II, III, IV).
- `annual_emissions_tons` — суммарный объём выбросов (т/год).
- `annual_waste_kg` — объём образования отходов (кг/год).
- `hazardous_waste_present` — есть ли отходы I–IV класса опасности.
- `hazardous_substances_class` — наличие веществ I–II класса опасности.
- `has_well` — есть ли артезианская/буровая скважина.
- `uses_surface_water` — осуществляется ли водозабор из рек/озёр.
- `livestock_byproducts` — образуется ли побочная продукция животноводства.
- `responsible_person_trained` — наличие действующего удостоверения об обучении.
- `responsible_person_count` — количество назначенных ответственных по экологии.
- Дополнительно: `need_instruction_docs` — ручной флаг, позволяющий включить требование №11 независимо от расчёта.

## 2. Базовые требования по категории НВОС

| Требование | Ключ | Условие назначения |
|------------|------|--------------------|
| Журнал учёта движения отходов | req_01 | `emission_category in [I, II, III, IV]` |
| Журнал стационарных источников | req_02 | `emission_category in [I, II, III]` |
| Статотчётность 2-ТП (воздух) | req_03 | `annual_emissions_tons > 5` |
| Статотчётность 2-ТП (отходы) | req_04 | `annual_waste_kg > 100` |
| Декларация о плате за НВОС | req_05 | `emission_category in [I, II, III]` |
| Отчёт по ПЭК | req_06 | `emission_category in [I, II, III]` |
| Отчёт инвентаризации выбросов | req_08 | `emission_category in [I, II, III, IV]` |
| Отчёт инвентаризации отходов | req_09 | `emission_category in [I, II, III, IV]` |
| Паспорта на отходы I–IV класса | req_10 | `hazardous_waste_present = true` |
| Инструкции по обращению с отходами | req_11 | `(hazardous_waste_present = true OR annual_waste_kg > 0) AND (responsible_person_count > 0 OR need_instruction_docs = true)` |
| НООЛР | req_12 | `emission_category in [I, II]` |
| НДВ | req_13 | `emission_category in [I, II]` |
| НДВ для опасных веществ | req_14 | `hazardous_substances_class in [I, II]` и `emission_category = III` |
| Экспертное заключение на НДВ | req_15 | `emission_category in [I, II, III]` |
| СЭЗ на проект НДВ | req_16 | `emission_category in [I, II, III]` |
| План НМУ | req_17 | `emission_category in [I, II, III]` |
| ППЭК | req_18 | `emission_category in [I, II, III]` |
| ДВОС | req_19 | `emission_category = II` |
| КЭР | req_20 | `emission_category = I` |
| Проект СЗЗ | req_21 | `emission_category in [I, II, III, IV]` |
| Решение об установлении СЗЗ | req_22 | `emission_category in [I, II, III, IV]` |

## 3. Дополнительные условия

### 3.1 Водопользование

- Если `has_well = true` → добавляем требование `water_01` (лицензия на право пользования недрами).
- Если `uses_surface_water = true` → добавляем требование `water_02` (решение/договор водопользования).

### 3.2 Побочные продукты животноводства

- Если `livestock_byproducts = true` → добавляем требование `byproduct_01` (технические условия на удобрения).

### 3.3 Обучение ответственных лиц

- Если `responsible_person_trained = false` или срок действия удостоверения истекает → формируем требование `training_01` (организовать обучение и получить удостоверение). Отражаем дату истечения и ответственного.

## 4. Нерешённые вопросы и допущения

- Требование `req_11` (инструкции и приказы): принято рабочее предположение — инструкции обязательны, если у клиента есть образование отходов (факт наличия отходов I–IV классов или ненулевой `annual_waste_kg`) и назначено хотя бы одно ответственное лицо. При необходимости админ может принудительно включить требование флагом `need_instruction_docs`. Требуется верификация нормативной ссылкой.
- Проверить, требуется ли учитывать дополнительные параметры (например, наличие радиоактивных веществ для `req_14`).
- Источники НПА для каждой записи должны быть добавлены после подготовки справочника (см. `docs/admin/reference-management.md`).

## 5. Псевдокод назначения

```pseudo
requirements = base_requirements_for(emission_category)

if annual_emissions_tons > 5:
    requirements.add(req_03)
if annual_waste_kg > 100:
    requirements.add(req_04)
if hazardous_waste_present:
    requirements.add(req_10)
if hazardous_substances_class in [I, II] and emission_category == 'III':
    requirements.add(req_14)

if has_well:
    requirements.add(water_01)
if uses_surface_water:
    requirements.add(water_02)
if livestock_byproducts:
    requirements.add(byproduct_01)
if not responsible_person_trained or training_expiry < today:
    requirements.add(training_01)
if (hazardous_waste_present or annual_waste_kg > 0) and (responsible_person_count > 0 or need_instruction_docs):
    requirements.add(req_11)
```

После расчёта требования маркируются статусом «Новая» и отображаются в блоке «Карта требований». Система должна поддерживать ручное добавление/исключение админом (см. админ-регламент).

## Чек-лист готовности

- [x] Описаны входные поля анкеты.
- [x] Настроены правила для всех требований из каталога.
- [x] Учитываются дополнительные условия (вода, побочные продукты, обучение).
- [ ] Подтверждено предположение по требованию `req_11` нормативной ссылкой.
