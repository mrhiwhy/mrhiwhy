<?php
// measurements/add.php
require_once '../config.php';

// Получаем список оборудования для выпадающего списка
$equipment_list = mysqli_query($conn, 
    "SELECT e.id, e.equipment_name, e.equipment_type, eb.block_number 
     FROM equipment e 
     JOIN energy_blocks eb ON e.block_id = eb.id 
     WHERE e.equipment_status = 'Исправен' 
     ORDER BY eb.block_number, e.equipment_name");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $equipment_id = safe_input($_POST['equipment_id']);
    $parameter_type = safe_input($_POST['parameter_type']);
    $parameter_name = safe_input($_POST['parameter_name']);
    $value = safe_input($_POST['value']);
    $unit = safe_input($_POST['unit']);
    $measured_at = safe_input($_POST['measured_at']);
    $is_alarm = isset($_POST['is_alarm']) ? 1 : 0;
    $alarm_type = safe_input($_POST['alarm_type']);
    $notes = safe_input($_POST['notes']);
    
    // Вставляем данные в БД
    $sql = "INSERT INTO measurements (equipment_id, parameter_type, parameter_name, value, unit, measured_at, is_alarm, alarm_type, notes) 
            VALUES ('$equipment_id', '$parameter_type', '$parameter_name', '$value', '$unit', '$measured_at', '$is_alarm', '$alarm_type', '$notes')";
    
    if(mysqli_query($conn, $sql)) {
        header("Location: index.php?message=added");
        exit();
    } else {
        $error = "Ошибка: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить показания - Беловская ГРЭС</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-industry fa-2x"></i>
                <div>
                    <h1>Беловская ГРЭС</h1>
                    <span>Добавление показаний</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="index.php"><i class="fas fa-chart-line"></i> Показания</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-plus-circle"></i> Добавить новые показания</h2>
            <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Назад к списку</a>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="equipment_id">Оборудование *</label>
                    <select id="equipment_id" name="equipment_id" required onchange="updateParameterSuggestions()">
                        <option value="">Выберите оборудование</option>
                        <?php while($eq = $equipment_list->fetch_assoc()): ?>
                        <option value="<?php echo $eq['id']; ?>">
                            <?php echo htmlspecialchars($eq['block_number'] . ' - ' . $eq['equipment_name'] . ' (' . $eq['equipment_type'] . ')'); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="parameter_type">Тип параметра *</label>
                        <select id="parameter_type" name="parameter_type" required onchange="updateParameterName()">
                            <option value="">Выберите тип</option>
                            <option value="Давление">Давление</option>
                            <option value="Температура">Температура</option>
                            <option value="Напряжение">Напряжение</option>
                            <option value="Ток">Ток</option>
                            <option value="Мощность">Мощность</option>
                            <option value="Расход">Расход</option>
                            <option value="Уровень">Уровень</option>
                            <option value="Вибрация">Вибрация</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="parameter_name">Наименование параметра *</label>
                        <input type="text" id="parameter_name" name="parameter_name" required list="parameter-suggestions">
                        <datalist id="parameter-suggestions">
                            <option value="Давление пара">
                            <option value="Температура пара">
                            <option value="Напряжение генератора">
                            <option value="Ток статора">
                            <option value="Мощность турбины">
                            <option value="Расход топлива">
                            <option value="Уровень воды в барабане">
                            <option value="Вибрация подшипника">
                        </datalist>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="value">Значение *</label>
                        <input type="number" id="value" name="value" step="0.001" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="unit">Единица измерения *</label>
                        <select id="unit" name="unit" required>
                            <option value="">Выберите единицу</option>
                            <option value="МПа">МПа</option>
                            <option value="°C">°C</option>
                            <option value="кВ">кВ</option>
                            <option value="А">А</option>
                            <option value="МВт">МВт</option>
                            <option value="т/ч">т/ч</option>
                            <option value="мм">мм</option>
                            <option value="мм/с">мм/с</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="measured_at">Время измерения *</label>
                        <input type="datetime-local" id="measured_at" name="measured_at" required 
                               value="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="alarm_type">Тип аварии</label>
                        <select id="alarm_type" name="alarm_type">
                            <option value="Нет">Нет</option>
                            <option value="Нижний предел">Нижний предел</option>
                            <option value="Верхний предел">Верхний предел</option>
                            <option value="Резкое изменение">Резкое изменение</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Примечания</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Дополнительная информация..."></textarea>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="is_alarm" name="is_alarm" onchange="toggleAlarmFields()">
                    <label for="is_alarm" style="font-weight: bold; color: #e74c3c;">
                        <i class="fas fa-exclamation-triangle"></i> Отметить как аварийное показание
                    </label>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" class="btn btn-success" style="padding: 12px 40px;">
                        <i class="fas fa-save"></i> Сохранить показания
                    </button>
                </div>
            </form>
        </div>

        <!-- Быстрые шаблоны -->
        <div style="margin-top: 40px; background: #f8f9fa; padding: 25px; border-radius: 8px;">
            <h3><i class="fas fa-bolt"></i> Быстрые шаблоны</h3>
            <p style="color: #666; margin-bottom: 15px;">Используйте готовые шаблоны для частых измерений:</p>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <button type="button" class="btn" onclick="fillTemplate('pressure')">
                    <i class="fas fa-tachometer-alt"></i> Давление пара
                </button>
                <button type="button" class="btn" onclick="fillTemplate('temperature')">
                    <i class="fas fa-thermometer-half"></i> Температура пара
                </button>
                <button type="button" class="btn" onclick="fillTemplate('power')">
                    <i class="fas fa-bolt"></i> Мощность турбины
                </button>
                <button type="button" class="btn" onclick="fillTemplate('vibration')">
                    <i class="fas fa-wave-square"></i> Вибрация
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
        function toggleAlarmFields() {
            const isAlarm = document.getElementById('is_alarm').checked;
            const alarmType = document.getElementById('alarm_type');
            
            if(isAlarm) {
                alarmType.value = 'Верхний предел';
                alarmType.disabled = false;
                document.getElementById('value').style.borderColor = '#e74c3c';
            } else {
                alarmType.value = 'Нет';
                alarmType.disabled = true;
                document.getElementById('value').style.borderColor = '';
            }
        }
        
        function updateParameterName() {
            const type = document.getElementById('parameter_type').value;
            const nameField = document.getElementById('parameter_name');
            
            if(type === 'Давление') nameField.value = 'Давление пара';
            else if(type === 'Температура') nameField.value = 'Температура пара';
            else if(type === 'Мощность') nameField.value = 'Мощность турбины';
            else if(type === 'Вибрация') nameField.value = 'Вибрация подшипника';
        }
        
        function updateParameterSuggestions() {
            const equipmentId = document.getElementById('equipment_id').value;
            // Здесь можно добавить AJAX запрос для получения типичных параметров для выбранного оборудования
        }
        
        function fillTemplate(template) {
            if(template === 'pressure') {
                document.getElementById('parameter_type').value = 'Давление';
                document.getElementById('parameter_name').value = 'Давление пара';
                document.getElementById('unit').value = 'МПа';
                document.getElementById('value').value = '13.7';
            } else if(template === 'temperature') {
                document.getElementById('parameter_type').value = 'Температура';
                document.getElementById('parameter_name').value = 'Температура пара';
                document.getElementById('unit').value = '°C';
                document.getElementById('value').value = '545';
            } else if(template === 'power') {
                document.getElementById('parameter_type').value = 'Мощность';
                document.getElementById('parameter_name').value = 'Мощность турбины';
                document.getElementById('unit').value = 'МВт';
                document.getElementById('value').value = '300';
            } else if(template === 'vibration') {
                document.getElementById('parameter_type').value = 'Вибрация';
                document.getElementById('parameter_name').value = 'Вибрация подшипника';
                document.getElementById('unit').value = 'мм/с';
                document.getElementById('value').value = '2.5';
            }
        }
        
        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            toggleAlarmFields();
        });
    </script>
</body>
</html>