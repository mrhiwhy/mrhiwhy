<?php
// equipment/edit.php
require_once '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Получаем данные оборудования
$sql = "SELECT e.*, eb.block_number, eb.block_name 
        FROM equipment e 
        JOIN energy_blocks eb ON e.block_id = eb.id 
        WHERE e.id = $id";
$result = mysqli_query($conn, $sql);
$equipment = mysqli_fetch_assoc($result);

if (!$equipment) {
    header("Location: index.php?error=notfound");
    exit();
}

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
    $last_maintenance = safe_input($_POST['last_maintenance']);
    $equipment_status = safe_input($_POST['equipment_status']);
    $parameters = safe_input($_POST['parameters']);

    // Обновляем данные в БД
    $update_sql = "UPDATE equipment SET 
                    equipment_code = '$equipment_code',
                    equipment_name = '$equipment_name',
                    equipment_type = '$equipment_type',
                    block_id = '$block_id',
                    manufacturer = '$manufacturer',
                    serial_number = '$serial_number',
                    installation_date = '$installation_date',
                    last_maintenance = '$last_maintenance',
                    equipment_status = '$equipment_status',
                    parameters = '$parameters'
                  WHERE id = $id";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: index.php?message=updated");
        exit();
    } else {
        $error = "Ошибка обновления: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать оборудование - Беловская ГРЭС</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .equipment-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e1e5eb;
        }

        .equipment-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .equipment-info h3 {
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .equipment-info p {
            color: #666;
            margin-bottom: 10px;
        }

        .param-input {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-left: 10px;
        }

        .status-working {
            background: #d4edda;
            color: #155724;
        }

        .status-repair {
            background: #f8d7da;
            color: #721c24;
        }

        .status-maintenance {
            background: #fff3cd;
            color: #856404;
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
                    <span>Редактирование оборудования</span>
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
            <h2><i class="fas fa-edit"></i> Редактирование оборудования</h2>
            <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Назад к списку</a>
        </div>

        <div class="equipment-header">
            <div class="equipment-icon">
                <?php
                $icons = [
                    'Котлоагрегат' => 'fas fa-fire',
                    'Турбина' => 'fas fa-cog',
                    'Генератор' => 'fas fa-bolt',
                    'Трансформатор' => 'fas fa-bolt',
                    'Насос' => 'fas fa-tint',
                    'Вентилятор' => 'fas fa-wind'
                ];
                $icon = $icons[$equipment['equipment_type']] ?? 'fas fa-cog';
                ?>
                <i class="<?php echo $icon; ?>"></i>
            </div>
            <div class="equipment-info">
                <h3><?php echo htmlspecialchars($equipment['equipment_name']); ?></h3>
                <p>Код: <strong><?php echo htmlspecialchars($equipment['equipment_code']); ?></strong></p>
                <p>
                    Текущий статус:
                    <span class="status-badge 
                        <?php
                        if ($equipment['equipment_status'] == 'Исправен')
                            echo 'status-working';
                        elseif ($equipment['equipment_status'] == 'В ремонте')
                            echo 'status-repair';
                        else
                            echo 'status-maintenance';
                        ?>">
                        <?php echo htmlspecialchars($equipment['equipment_status']); ?>
                    </span>
                </p>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="equipment_code"><i class="fas fa-barcode"></i> Код оборудования *</label>
                        <input type="text" id="equipment_code" name="equipment_code"
                            value="<?php echo htmlspecialchars($equipment['equipment_code']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="equipment_name"><i class="fas fa-tag"></i> Наименование *</label>
                        <input type="text" id="equipment_name" name="equipment_name"
                            value="<?php echo htmlspecialchars($equipment['equipment_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="equipment_type"><i class="fas fa-cog"></i> Тип оборудования *</label>
                        <select id="equipment_type" name="equipment_type" required onchange="updateEquipmentIcon()">
                            <?php
                            $types = ['Котлоагрегат', 'Турбина', 'Генератор', 'Трансформатор', 'Насос', 'Вентилятор', 'Другое'];
                            foreach ($types as $type):
                                $selected = ($equipment['equipment_type'] == $type) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $type; ?>" <?php echo $selected; ?>>
                                    <?php echo $type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="block_id"><i class="fas fa-bolt"></i> Энергоблок *</label>
                        <select id="block_id" name="block_id" required>
                            <option value="">Выберите энергоблок</option>
                            <?php
                            while ($block = $blocks->fetch_assoc()):
                                $selected = ($equipment['block_id'] == $block['id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $block['id']; ?>" <?php echo $selected; ?>>
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
                            value="<?php echo htmlspecialchars($equipment['manufacturer']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="serial_number"><i class="fas fa-hashtag"></i> Заводской номер</label>
                        <input type="text" id="serial_number" name="serial_number"
                            value="<?php echo htmlspecialchars($equipment['serial_number']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="installation_date"><i class="fas fa-calendar-plus"></i> Дата установки</label>
                        <input type="date" id="installation_date" name="installation_date"
                            value="<?php echo htmlspecialchars($equipment['installation_date']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_maintenance"><i class="fas fa-calendar-check"></i> Последнее ТО</label>
                        <input type="date" id="last_maintenance" name="last_maintenance"
                            value="<?php echo htmlspecialchars($equipment['last_maintenance']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="equipment_status"><i class="fas fa-chart-line"></i> Состояние *</label>
                        <select id="equipment_status" name="equipment_status" required>
                            <?php
                            $statuses = ['Исправен', 'Требует ремонта', 'В ремонте', 'Списано'];
                            foreach ($statuses as $status):
                                $selected = ($equipment['equipment_status'] == $status) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $status; ?>" <?php echo $selected; ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="parameters"><i class="fas fa-sliders-h"></i> Технические параметры (JSON)</label>
                    <textarea id="parameters" name="parameters" rows="4" class="param-input"
                        placeholder='{"давление": "13.7 МПа", "температура": "545 °C", "мощность": "300 МВт"}'>
                        <?php echo htmlspecialchars($equipment['parameters']); ?>
                    </textarea>
                    <small style="color: #666;">Укажите параметры в формате JSON</small>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" class="btn btn-success" style="padding: 12px 40px;">
                        <i class="fas fa-save"></i> Сохранить изменения
                    </button>
                    <a href="index.php" class="btn" style="margin-left: 15px;">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>

        <!-- Быстрые шаблоны параметров -->
        <div style="margin-top: 40px; background: #f8f9fa; padding: 25px; border-radius: 8px;">
            <h4><i class="fas fa-magic"></i> Быстрые шаблоны параметров</h4>
            <p style="color: #666; margin-bottom: 15px;">Выберите тип оборудования для автоматического заполнения:</p>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="btn" onclick="fillTemplate('boiler')">
                    <i class="fas fa-fire"></i> Котлоагрегат
                </button>
                <button type="button" class="btn" onclick="fillTemplate('turbine')">
                    <i class="fas fa-cog"></i> Турбина
                </button>
                <button type="button" class="btn" onclick="fillTemplate('generator')">
                    <i class="fas fa-bolt"></i> Генератор
                </button>
                <button type="button" class="btn" onclick="fillTemplate('pump')">
                    <i class="fas fa-tint"></i> Насос
                </button>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <script>
        function updateEquipmentIcon() {
            const type = document.getElementById('equipment_type').value;
            const iconMap = {
                'Котлоагрегат': 'fas fa-fire',
                'Турбина': 'fas fa-cog',
                'Генератор': 'fas fa-bolt',
                'Трансформатор': 'fas fa-bolt',
                'Насос': 'fas fa-tint',
                'Вентилятор': 'fas fa-wind',
                'Другое': 'fas fa-cog'
            };

            const iconElement = document.querySelector('.equipment-icon i');
            iconElement.className = iconMap[type] || 'fas fa-cog';

            // Меняем цвет иконки
            document.querySelector('.equipment-icon').style.background =
                type === 'Котлоагрегат' ? 'linear-gradient(135deg, #e74c3c, #c0392b)' :
                    type === 'Турбина' ? 'linear-gradient(135deg, #3498db, #2980b9)' :
                        type === 'Генератор' ? 'linear-gradient(135deg, #f39c12, #d35400)' :
                            'linear-gradient(135deg, #2ecc71, #27ae60)';
        }

        function fillTemplate(template) {
            const paramsField = document.getElementById('parameters');

            const templates = {
                'boiler': '{\n  "давление": "13.7 МПа",\n  "температура": "545 °C",\n  "производительность": "420 т/ч",\n  "тип_топлива": "уголь"\n}',
                'turbine': '{\n  "мощность": "300 МВт",\n  "обороты": "3000 об/мин",\n  "давление_входа": "12.7 МПа",\n  "температура_входа": "540 °C"\n}',
                'generator': '{\n  "мощность": "320 МВА",\n  "напряжение": "20 кВ",\n  "ток": "9240 А",\n  "частота": "50 Гц"\n}',
                'pump': '{\n  "производительность": "580 м³/ч",\n  "напряжение": "6 кВ",\n  "мощность": "5000 кВт",\n  "давление_напора": "15 МПа"\n}'
            };

            if (templates[template]) {
                paramsField.value = templates[template];
                alert('Шаблон параметров загружен!');
            }
        }

        // Валидация JSON при вводе
        document.getElementById('parameters').addEventListener('blur', function () {
            try {
                const json = JSON.parse(this.value);
                this.style.borderColor = '#27ae60';
                this.style.backgroundColor = '#f8fff8';
            } catch (e) {
                this.style.borderColor = '#e74c3c';
                this.style.backgroundColor = '#fff8f8';
                alert('Ошибка в формате JSON. Исправьте или используйте шаблон.');
            }
        });

        // Инициализация
        document.addEventListener('DOMContentLoaded', function () {
            // Установка сегодняшней даты для ТО, если поле пустое
            const lastMaintenance = document.getElementById('last_maintenance');
            if (!lastMaintenance.value) {
                lastMaintenance.valueAsDate = new Date();
            }
        });
    </script>
</body>

</html>