<?php
// equipment/add.php
require_once '../config.php';

// Получаем список энергоблоков для выпадающего списка
$blocks = mysqli_query($conn, "SELECT id, block_number, block_name FROM energy_blocks ORDER BY block_number");

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $equipment_code = safe_input($_POST['equipment_code']);
    $equipment_name = safe_input($_POST['equipment_name']);
    $equipment_type = safe_input($_POST['equipment_type']);
    $block_id = safe_input($_POST['block_id']);
    $manufacturer = safe_input($_POST['manufacturer']);
    $serial_number = safe_input($_POST['serial_number']);
    $installation_date = safe_input($_POST['installation_date']);
    $equipment_status = safe_input($_POST['equipment_status']);
    $parameters = safe_input($_POST['parameters']);

    // Вставляем данные в БД
    $sql = "INSERT INTO equipment (equipment_code, equipment_name, equipment_type, block_id, 
                                   manufacturer, serial_number, installation_date, 
                                   equipment_status, parameters, created_at) 
            VALUES ('$equipment_code', '$equipment_name', '$equipment_type', '$block_id',
                    '$manufacturer', '$serial_number', '$installation_date',
                    '$equipment_status', '$parameters', NOW())";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?message=added");
        exit();
    } else {
        $error = "Ошибка добавления: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить оборудование - Беловская ГРЭС</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-header-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }

        .generator-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px dashed #ddd;
        }

        .generator-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .preview-box {
            background: #fff;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            display: none;
        }

        .preview-box.show {
            display: block;
        }

        .preview-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .code-snippet {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-industry fa-2x"></i>
                <div>
                    <h1>Беловская ГРЭС</h1>
                    <span>Добавление оборудования</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="index.php"><i class="fas fa-cogs"></i> Оборудование</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-plus-circle"></i> Добавить новое оборудование</h2>
            <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Назад к списку</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <div class="form-header">
                <div class="form-header-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: #2c3e50;">Новое оборудование</h3>
                    <p style="color: #666; margin: 5px 0 0 0;">Заполните все обязательные поля (*)</p>
                </div>
            </div>

            <form method="POST" action="" id="equipmentForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="equipment_code"><i class="fas fa-barcode"></i> Код оборудования *</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="equipment_code" name="equipment_code" required style="flex: 1;">
                            <button type="button" class="btn" onclick="generateCode()" style="white-space: nowrap;">
                                <i class="fas fa-sync-alt"></i> Сгенерировать
                            </button>
                        </div>
                        <small style="color: #666;">Например: КОТ-001, ТУР-002, ГЕН-003</small>
                    </div>

                    <div class="form-group">
                        <label for="equipment_name"><i class="fas fa-tag"></i> Наименование *</label>
                        <input type="text" id="equipment_name" name="equipment_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="equipment_type"><i class="fas fa-cog"></i> Тип оборудования *</label>
                        <select id="equipment_type" name="equipment_type" required onchange="updateTemplate()">
                            <option value="">Выберите тип</option>
                            <option value="Котлоагрегат">Котлоагрегат</option>
                            <option value="Турбина">Турбина</option>
                            <option value="Генератор">Генератор</option>
                            <option value="Трансформатор">Трансформатор</option>
                            <option value="Насос">Насос</option>
                            <option value="Вентилятор">Вентилятор</option>
                            <option value="Другое">Другое</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="block_id"><i class="fas fa-bolt"></i> Энергоблок *</label>
                        <select id="block_id" name="block_id" required>
                            <option value="">Выберите энергоблок</option>
                            <?php while ($block = $blocks->fetch_assoc()): ?>
                                <option value="<?php echo $block['id']; ?>">
                                    <?php echo htmlspecialchars($block['block_number'] . ' - ' . $block['block_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="manufacturer"><i class="fas fa-industry"></i> Производитель</label>
                        <input type="text" id="manufacturer" name="manufacturer"
                            placeholder="Например: Таганрогский котельный завод">
                    </div>

                    <div class="form-group">
                        <label for="serial_number"><i class="fas fa-hashtag"></i> Заводской номер</label>
                        <input type="text" id="serial_number" name="serial_number" placeholder="Например: ТП80-4567">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="installation_date"><i class="fas fa-calendar-plus"></i> Дата установки</label>
                        <input type="date" id="installation_date" name="installation_date">
                    </div>

                    <div class="form-group">
                        <label for="equipment_status"><i class="fas fa-chart-line"></i> Состояние *</label>
                        <select id="equipment_status" name="equipment_status" required>
                            <option value="Исправен" selected>Исправен</option>
                            <option value="Требует ремонта">Требует ремонта</option>
                            <option value="В ремонте">В ремонте</option>
                            <option value="Списано">Списано</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="parameters"><i class="fas fa-sliders-h"></i> Технические параметры (JSON)</label>
                    <textarea id="parameters" name="parameters" rows="4" class="param-input"
                        placeholder='{"давление": "13.7 МПа", "температура": "545 °C", "мощность": "300 МВт"}'></textarea>
                    <small style="color: #666;">Укажите параметры в формате JSON или используйте шаблон</small>
                </div>

                <!-- Генератор шаблонов -->
                <div class="generator-box">
                    <div class="preview-title"><i class="fas fa-magic"></i> Быстрые шаблоны</div>
                    <p style="color: #666; margin-bottom: 10px;">Выберите тип оборудования для автоматического
                        заполнения:</p>

                    <div class="generator-buttons">
                        <button type="button" class="btn" onclick="useTemplate('boiler')">
                            <i class="fas fa-fire"></i> Котлоагрегат
                        </button>
                        <button type="button" class="btn" onclick="useTemplate('turbine')">
                            <i class="fas fa-cog"></i> Турбина
                        </button>
                        <button type="button" class="btn" onclick="useTemplate('generator')">
                            <i class="fas fa-bolt"></i> Генератор
                        </button>
                        <button type="button" class="btn" onclick="useTemplate('transformer')">
                            <i class="fas fa-bolt"></i> Трансформатор
                        </button>
                        <button type="button" class="btn" onclick="useTemplate('pump')">
                            <i class="fas fa-tint"></i> Насос
                        </button>
                        <button type="button" class="btn" onclick="useTemplate('fan')">
                            <i class="fas fa-wind"></i> Вентилятор
                        </button>
                    </div>

                    <div id="previewBox" class="preview-box">
                        <div class="preview-title">Предпросмотр данных:</div>
                        <div id="previewContent"></div>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" class="btn btn-success" style="padding: 12px 40px;">
                        <i class="fas fa-save"></i> Сохранить оборудование
                    </button>
                    <button type="reset" class="btn" style="margin-left: 15px;">
                        <i class="fas fa-redo"></i> Очистить форму
                    </button>
                </div>
            </form>
        </div>

        <!-- Инструкция -->
        <div style="margin-top: 40px; background: #f8f9fa; padding: 25px; border-radius: 8px;">
            <h3><i class="fas fa-info-circle"></i> Как заполнять форму:</h3>
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 15px;">
                <div>
                    <h4><i class="fas fa-barcode"></i> Код оборудования</h4>
                    <p>Используйте префиксы: КОТ- для котлов, ТУР- для турбин, ГЕН- для генераторов</p>
                </div>
                <div>
                    <h4><i class="fas fa-cog"></i> Технические параметры</h4>
                    <p>Используйте кнопки шаблонов для автоматического заполнения параметров в формате JSON</p>
                </div>
                <div>
                    <h4><i class="fas fa-calendar"></i> Даты</h4>
                    <p>Дата установки и последнего ТО заполняются автоматически текущей датой</p>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <script>
        // Генератор кода оборудования
        function generateCode() {
            const type = document.getElementById('equipment_type').value;
            const codeField = document.getElementById('equipment_code');

            if (!type) {
                alert('Сначала выберите тип оборудования');
                return;
            }

            // Префиксы для разных типов
            const prefixes = {
                'Котлоагрегат': 'КОТ',
                'Турбина': 'ТУР',
                'Генератор': 'ГЕН',
                'Трансформатор': 'ТРА',
                'Насос': 'НАС',
                'Вентилятор': 'ВЕН',
                'Другое': 'ОБ'
            };

            const prefix = prefixes[type] || 'ОБ';

            // Генерируем случайный номер
            const randomNum = Math.floor(Math.random() * 900) + 100;
            codeField.value = prefix + '-' + randomNum;
        }

        // Шаблоны для разных типов оборудования
        const templates = {
            'boiler': {
                name: 'Котлоагрегат ТП-80',
                params: '{\n  "давление": "13.7 МПа",\n  "температура": "545 °C",\n  "производительность": "420 т/ч",\n  "тип_топлива": "уголь",\n  "КПД": "92%"\n}',
                manufacturer: 'Таганрогский котельный завод'
            },
            'turbine': {
                name: 'Турбина К-300',
                params: '{\n  "мощность": "300 МВт",\n  "обороты": "3000 об/мин",\n  "давление_входа": "12.7 МПа",\n  "температура_входа": "540 °C",\n  "расход_пара": "1000 т/ч"\n}',
                manufacturer: 'Ленинградский металлический завод'
            },
            'generator': {
                name: 'Турбогенератор ТВВ-320',
                params: '{\n  "мощность": "320 МВА",\n  "напряжение": "20 кВ",\n  "ток": "9240 А",\n  "частота": "50 Гц",\n  "cos_φ": "0.85"\n}',
                manufacturer: 'Электросила'
            },
            'transformer': {
                name: 'Трансформатор ТДЦ-400000/220',
                params: '{\n  "мощность": "400 МВА",\n  "напряжение_ВН": "242 кВ",\n  "напряжение_НН": "20 кВ",\n  "схема_соединения": "Y/Δ-11"\n}',
                manufacturer: 'Запорожтрансформатор'
            },
            'pump': {
                name: 'Питательный насос ПЭ-580',
                params: '{\n  "производительность": "580 м³/ч",\n  "напряжение": "6 кВ",\n  "мощность": "5000 кВт",\n  "давление_напора": "15 МПа"\n}',
                manufacturer: 'Лысьвенский завод'
            },
            'fan': {
                name: 'Дутьевой вентилятор ВДН-32',
                params: '{\n  "производительность": "650000 м³/ч",\n  "мощность": "2500 кВт",\n  "напряжение": "6 кВ",\n  "обороты": "750 об/мин"\n}',
                manufacturer: 'Уральский компрессорный завод'
            }
        };

        // Использование шаблона
        function useTemplate(templateKey) {
            const template = templates[templateKey];
            if (!template) return;

            // Заполняем поля формы
            document.getElementById('equipment_name').value = template.name;
            document.getElementById('manufacturer').value = template.manufacturer;
            document.getElementById('parameters').value = template.params;

            // Устанавливаем тип оборудования
            const typeMap = {
                'boiler': 'Котлоагрегат',
                'turbine': 'Турбина',
                'generator': 'Генератор',
                'transformer': 'Трансформатор',
                'pump': 'Насос',
                'fan': 'Вентилятор'
            };
            document.getElementById('equipment_type').value = typeMap[templateKey];

            // Генерируем код
            generateCode();

            // Показываем предпросмотр
            showPreview(template);
        }

        // Показать предпросмотр
        function showPreview(template) {
            const previewBox = document.getElementById('previewBox');
            const previewContent = document.getElementById('previewContent');

            previewContent.innerHTML = `
                <div><strong>Наименование:</strong> ${template.name}</div>
                <div><strong>Производитель:</strong> ${template.manufacturer}</div>
                <div class="code-snippet">${template.params}</div>
            `;

            previewBox.classList.add('show');
        }

        // Обновление шаблона при изменении типа
        function updateTemplate() {
            const type = document.getElementById('equipment_type').value;
            const templateMap = {
                'Котлоагрегат': 'boiler',
                'Турбина': 'turbine',
                'Генератор': 'generator',
                'Трансформатор': 'transformer',
                'Насос': 'pump',
                'Вентилятор': 'fan'
            };

            if (templateMap[type]) {
                useTemplate(templateMap[type]);
            }
        }

        // Валидация формы
        document.getElementById('equipmentForm').addEventListener('submit', function (e) {
            const code = document.getElementById('equipment_code').value;
            const name = document.getElementById('equipment_name').value;
            const type = document.getElementById('equipment_type').value;
            const block = document.getElementById('block_id').value;

            if (!code || !name || !type || !block) {
                e.preventDefault();
                alert('Заполните все обязательные поля (отмечены *)');
                return false;
            }

            // Валидация JSON
            const paramsField = document.getElementById('parameters');
            if (paramsField.value.trim()) {
                try {
                    JSON.parse(paramsField.value);
                } catch (err) {
                    e.preventDefault();
                    alert('Ошибка в формате JSON параметров. Исправьте или очистите поле.');
                    paramsField.focus();
                    return false;
                }
            }

            return true;
        });

        // Инициализация
        document.addEventListener('DOMContentLoaded', function () {
            // Установка сегодняшней даты
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('installation_date').value = today;

            // Автофокус на поле кода
            document.getElementById('equipment_code').focus();
        });
    </script>
</body>

</html>