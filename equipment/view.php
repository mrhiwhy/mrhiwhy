<?php
// equipment/view.php
require_once '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT e.*, eb.block_number, eb.block_name, eb.power_mw as block_power
        FROM equipment e 
        JOIN energy_blocks eb ON e.block_id = eb.id 
        WHERE e.id = $id";
$result = mysqli_query($conn, $sql);
$equipment = mysqli_fetch_assoc($result);

if (!$equipment) {
    header("Location: index.php?error=notfound");
    exit();
}

// Получаем последние показания для этого оборудования
$measurements = mysqli_query(
    $conn,
    "SELECT * FROM measurements 
     WHERE equipment_id = $id 
     ORDER BY measured_at DESC 
     LIMIT 5"
);

// Парсим параметры JSON
$parameters = [];
if ($equipment['parameters']) {
    $parameters = json_decode($equipment['parameters'], true);
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр оборудования - Беловская ГРЭС</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .equipment-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 0 auto;
        }

        .equipment-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px solid #e1e5eb;
        }

        .equipment-icon-large {
            width: 100px;
            height: 100px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            flex-shrink: 0;
        }

        .equipment-title h2 {
            margin: 0;
            color: #2c3e50;
        }

        .equipment-title .badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 10px;
            font-weight: 500;
        }

        .info-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .section-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #3498db;
        }

        .section-card h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .param-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .param-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e1e5eb;
            display: flex;
            justify-content: space-between;
        }

        .param-list li:last-child {
            border-bottom: none;
        }

        .param-name {
            color: #555;
        }

        .param-value {
            font-weight: 500;
            color: #2c3e50;
        }

        .measurements-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .measurements-table th {
            background: #e1f5fe;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }

        .measurements-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e1e5eb;
        }

        .action-bar {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5eb;
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
                    <span>Карточка оборудования</span>
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
            <h2><i class="fas fa-tools"></i> Карточка оборудования</h2>
            <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Назад к списку</a>
        </div>

        <div class="equipment-card">
            <div class="equipment-header">
                <?php
                $iconMap = [
                    'Котлоагрегат' => ['fas fa-fire', '#e74c3c'],
                    'Турбина' => ['fas fa-cog', '#3498db'],
                    'Генератор' => ['fas fa-bolt', '#f39c12'],
                    'Трансформатор' => ['fas fa-bolt', '#9b59b6'],
                    'Насос' => ['fas fa-tint', '#2ecc71'],
                    'Вентилятор' => ['fas fa-wind', '#1abc9c']
                ];
                $icon = $iconMap[$equipment['equipment_type']] ?? ['fas fa-cog', '#95a5a6'];
                ?>
                <div class="equipment-icon-large"
                    style="background: linear-gradient(135deg, <?php echo $icon[1]; ?>, <?php echo $icon[1]; ?>88);">
                    <i class="<?php echo $icon[0]; ?>"></i>
                </div>
                <div class="equipment-title">
                    <h2><?php echo htmlspecialchars($equipment['equipment_name']); ?></h2>
                    <div class="badge" style="background: 
                        <?php
                        if ($equipment['equipment_status'] == 'Исправен')
                            echo '#d4edda; color: #155724';
                        elseif ($equipment['equipment_status'] == 'В ремонте')
                            echo '#f8d7da; color: #721c24';
                        else
                            echo '#fff3cd; color: #856404';
                        ?>">
                        <?php echo htmlspecialchars($equipment['equipment_status']); ?>
                    </div>
                </div>
            </div>

            <div class="info-sections">
                <div class="section-card">
                    <h3><i class="fas fa-info-circle"></i> Основная информация</h3>
                    <ul class="param-list">
                        <li><span class="param-name">Код оборудования:</span> <span
                                class="param-value"><?php echo htmlspecialchars($equipment['equipment_code']); ?></span>
                        </li>
                        <li><span class="param-name">Тип:</span> <span
                                class="param-value"><?php echo htmlspecialchars($equipment['equipment_type']); ?></span>
                        </li>
                        <li><span class="param-name">Энергоблок:</span> <span
                                class="param-value"><?php echo htmlspecialchars($equipment['block_number'] . ' - ' . $equipment['block_name']); ?></span>
                        </li>
                        <li><span class="param-name">Мощность блока:</span> <span
                                class="param-value"><?php echo $equipment['block_power']; ?> МВт</span></li>
                    </ul>
                </div>

                <div class="section-card">
                    <h3><i class="fas fa-industry"></i> Производственная информация</h3>
                    <ul class="param-list">
                        <li><span class="param-name">Производитель:</span> <span
                                class="param-value"><?php echo $equipment['manufacturer'] ? htmlspecialchars($equipment['manufacturer']) : '—'; ?></span>
                        </li>
                        <li><span class="param-name">Заводской номер:</span> <span
                                class="param-value"><?php echo $equipment['serial_number'] ? htmlspecialchars($equipment['serial_number']) : '—'; ?></span>
                        </li>
                        <li><span class="param-name">Дата установки:</span> <span
                                class="param-value"><?php echo $equipment['installation_date'] ? date('d.m.Y', strtotime($equipment['installation_date'])) : '—'; ?></span>
                        </li>
                        <li><span class="param-name">Последнее ТО:</span> <span
                                class="param-value"><?php echo $equipment['last_maintenance'] ? date('d.m.Y', strtotime($equipment['last_maintenance'])) : '—'; ?></span>
                        </li>
                    </ul>
                </div>

                <?php if (!empty($parameters)): ?>
                    <div class="section-card" style="grid-column: span 2;">
                        <h3><i class="fas fa-sliders-h"></i> Технические параметры</h3>
                        <div
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                            <?php foreach ($parameters as $key => $value): ?>
                                <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e1e5eb;">
                                    <div style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($key); ?>
                                    </div>
                                    <div style="font-size: 1.1rem; font-weight: 500; color: #2c3e50;">
                                        <?php echo htmlspecialchars($value); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (mysqli_num_rows($measurements) > 0): ?>
                    <div class="section-card" style="grid-column: span 2;">
                        <h3><i class="fas fa-chart-line"></i> Последние показания</h3>
                        <table class="measurements-table">
                            <thead>
                                <tr>
                                    <th>Время</th>
                                    <th>Параметр</th>
                                    <th>Значение</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($measurement = $measurements->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('H:i d.m', strtotime($measurement['measured_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($measurement['parameter_name']); ?></td>
                                        <td><strong><?php echo $measurement['value']; ?>
                                                <?php echo htmlspecialchars($measurement['unit']); ?></strong></td>
                                        <td>
                                            <?php if ($measurement['is_alarm']): ?>
                                                <span style="color: #e74c3c; font-weight: 500;">
                                                    <i class="fas fa-exclamation-triangle"></i> Авария
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #27ae60;">
                                                    <i class="fas fa-check-circle"></i> Норма
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="../measurements/index.php?equipment=<?php echo $equipment['id']; ?>" class="btn">
                                <i class="fas fa-chart-bar"></i> Все показания
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="action-bar">
                <a href="edit.php?id=<?php echo $equipment['id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <a href="index.php?delete=<?php echo $equipment['id']; ?>" class="btn btn-danger"
                    onclick="return confirm('Удалить оборудование <?php echo addslashes($equipment['equipment_name']); ?>?')">
                    <i class="fas fa-trash"></i> Удалить
                </a>
                <a href="../measurements/add.php?equipment_id=<?php echo $equipment['id']; ?>" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Добавить показания
                </a>
                <a href="#" class="btn" onclick="window.print()">
                    <i class="fas fa-print"></i> Печать
                </a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
</body>

</html>