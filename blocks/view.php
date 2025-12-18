<?php
// blocks/view.php
require_once '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Получаем данные энергоблока
$sql = "SELECT eb.*, p.full_name as responsible_name 
        FROM energy_blocks eb 
        LEFT JOIN personnel p ON eb.responsible_personnel_id = p.id 
        WHERE eb.id = $id";
$result = mysqli_query($conn, $sql);
$block = mysqli_fetch_assoc($result);

if (!$block) {
    header("Location: index.php?error=notfound");
    exit();
}

// Получаем оборудование этого блока
$equipment = mysqli_query(
    $conn,
    "SELECT * FROM equipment 
     WHERE block_id = $id 
     ORDER BY equipment_type, equipment_name"
);

// Получаем статистику по оборудованию
$stats = mysqli_query(
    $conn,
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN equipment_status = 'Исправен' THEN 1 ELSE 0 END) as working,
        SUM(CASE WHEN equipment_status = 'В ремонте' THEN 1 ELSE 0 END) as repairing,
        SUM(CASE WHEN equipment_status = 'Требует ремонта' THEN 1 ELSE 0 END) as needs_repair
     FROM equipment 
     WHERE block_id = $id"
)->fetch_assoc();

// Получаем последние показания для этого блока
$measurements = mysqli_query(
    $conn,
    "SELECT m.*, e.equipment_name 
     FROM measurements m 
     JOIN equipment e ON m.equipment_id = e.id 
     WHERE e.block_id = $id 
     ORDER BY m.measured_at DESC 
     LIMIT 10"
);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр энергоблока - Беловская ГРЭС</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .block-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: 0 auto;
        }

        .block-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px solid #e1e5eb;
        }

        .block-icon-large {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            flex-shrink: 0;
        }

        .block-title h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.8rem;
        }

        .block-stats {
            display: flex;
            gap: 30px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            min-width: 100px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #3498db;
        }

        .info-card h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e1e5eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #555;
        }

        .info-value {
            font-weight: 500;
            color: #2c3e50;
        }

        .equipment-section {
            margin-top: 40px;
        }

        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .equipment-item {
            background: white;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s;
        }

        .equipment-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: #3498db;
        }

        .equipment-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .equipment-icon-small {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .equipment-name {
            font-weight: 500;
            color: #2c3e50;
            font-size: 1rem;
        }

        .equipment-type {
            font-size: 0.85rem;
            color: #666;
        }

        .equipment-status {
            margin-top: 10px;
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
            flex-wrap: wrap;
        }

        .progress-bar {
            height: 8px;
            background: #e1e5eb;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            background: #27ae60;
            transition: width 0.3s;
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
                    <span>Карточка энергоблока</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="index.php"><i class="fas fa-bolt"></i> Энергоблоки</a></li>
                    <li><a href="../equipment/index.php"><i class="fas fa-cogs"></i> Оборудование</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-bolt"></i> Карточка энергоблока</h2>
            <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Назад к списку</a>
        </div>

        <div class="block-card">
            <div class="block-header">
                <div class="block-icon-large">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="block-title">
                    <h2><?php echo htmlspecialchars($block['block_number'] . ' - ' . $block['block_name']); ?></h2>
                    <div style="color: #666; margin-top: 5px;">
                        <?php echo htmlspecialchars($block['block_type']); ?> •
                        <?php echo $block['power_mw']; ?> МВт •
                        Год ввода: <?php echo $block['commission_year']; ?>
                    </div>

                    <div class="block-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $stats['total']; ?></span>
                            <span class="stat-label">Оборудования</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value" style="color: #27ae60;"><?php echo $stats['working']; ?></span>
                            <span class="stat-label">Исправно</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value" style="color: #f39c12;"><?php echo $stats['repairing']; ?></span>
                            <span class="stat-label">В ремонте</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"
                                style="color: #e74c3c;"><?php echo $stats['needs_repair']; ?></span>
                            <span class="stat-label">Требует ремонта</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Основная информация</h3>
                    <div class="info-row">
                        <span class="info-label">Номер блока:</span>
                        <span class="info-value"><?php echo htmlspecialchars($block['block_number']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Название:</span>
                        <span class="info-value"><?php echo htmlspecialchars($block['block_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Тип:</span>
                        <span class="info-value"><?php echo htmlspecialchars($block['block_type']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Мощность:</span>
                        <span class="info-value"><?php echo $block['power_mw']; ?> МВт</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Год ввода:</span>
                        <span class="info-value"><?php echo $block['commission_year']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Местоположение:</span>
                        <span
                            class="info-value"><?php echo htmlspecialchars($block['location'] ?: 'Основной корпус'); ?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-chart-line"></i> Статус и ответственные</h3>
                    <div class="info-row">
                        <span class="info-label">Статус:</span>
                        <span class="info-value">
                            <span class="status <?php
                            if ($block['status'] == 'В работе')
                                echo 'status-working';
                            elseif ($block['status'] == 'В ремонте')
                                echo 'status-repair';
                            else
                                echo 'status-vacation';
                            ?>">
                                <?php echo htmlspecialchars($block['status']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ответственный:</span>
                        <span class="info-value">
                            <?php echo $block['responsible_name'] ? htmlspecialchars($block['responsible_name']) : 'Не назначен'; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Исправность:</span>
                        <span class="info-value">
                            <?php
                            $workingPercentage = $stats['total'] > 0
                                ? round(($stats['working'] / $stats['total']) * 100, 1)
                                : 0;
                            echo $workingPercentage; ?>%
                        </span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $workingPercentage; ?>%; 
                            background: <?php
                            if ($workingPercentage > 80)
                                echo '#27ae60';
                            elseif ($workingPercentage > 50)
                                echo '#f39c12';
                            else
                                echo '#e74c3c';
                            ?>;">
                        </div>
                    </div>
                    <?php if ($block['notes']): ?>
                        <div class="info-row">
                            <span class="info-label">Примечания:</span>
                            <span class="info-value"><?php echo htmlspecialchars($block['notes']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Оборудование блока -->
            <div class="equipment-section">
                <h3><i class="fas fa-cogs"></i> Оборудование энергоблока</h3>

                <?php if (mysqli_num_rows($equipment) > 0): ?>
                    <div class="equipment-grid">
                        <?php while ($eq = $equipment->fetch_assoc()):
                            $iconMap = [
                                'Котлоагрегат' => ['fas fa-fire', '#e74c3c'],
                                'Турбина' => ['fas fa-cog', '#3498db'],
                                'Генератор' => ['fas fa-bolt', '#f39c12'],
                                'Трансформатор' => ['fas fa-bolt', '#9b59b6'],
                                'Насос' => ['fas fa-tint', '#2ecc71'],
                                'Вентилятор' => ['fas fa-wind', '#1abc9c']
                            ];
                            $icon = $iconMap[$eq['equipment_type']] ?? ['fas fa-cog', '#95a5a6'];

                            $statusClass = 'status-working';
                            if ($eq['equipment_status'] == 'Требует ремонта')
                                $statusClass = 'status-vacation';
                            elseif ($eq['equipment_status'] == 'В ремонте')
                                $statusClass = 'status-repair';
                            elseif ($eq['equipment_status'] == 'Списано')
                                $statusClass = 'status-repair';
                            ?>
                            <div class="equipment-item">
                                <div class="equipment-header">
                                    <div class="equipment-icon-small"
                                        style="background: linear-gradient(135deg, <?php echo $icon[1]; ?>, <?php echo $icon[1]; ?>88);">
                                        <i class="<?php echo $icon[0]; ?>"></i>
                                    </div>
                                    <div>
                                        <div class="equipment-name"><?php echo htmlspecialchars($eq['equipment_name']); ?></div>
                                        <div class="equipment-type"><?php echo htmlspecialchars($eq['equipment_type']); ?></div>
                                    </div>
                                </div>

                                <div class="equipment-status">
                                    <span class="status <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($eq['equipment_status']); ?>
                                    </span>
                                </div>

                                <div style="margin-top: 15px; font-size: 0.85rem; color: #666;">
                                    <div>Код: <strong><?php echo htmlspecialchars($eq['equipment_code']); ?></strong></div>
                                    <?php if ($eq['last_maintenance']): ?>
                                        <div>ТО: <?php echo date('d.m.Y', strtotime($eq['last_maintenance'])); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div style="margin-top: 15px; display: flex; gap: 10px;">
                                    <a href="../equipment/view.php?id=<?php echo $eq['id']; ?>" class="btn-action btn-view"
                                        title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../equipment/edit.php?id=<?php echo $eq['id']; ?>" class="btn-action btn-edit"
                                        title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../measurements/add.php?equipment_id=<?php echo $eq['id']; ?>" class="btn-action"
                                        title="Добавить показания" style="background: #2ecc71; color: white;">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div
                        style="background: #f8f9fa; padding: 30px; text-align: center; border-radius: 8px; margin-top: 20px;">
                        <i class="fas fa-cogs fa-3x" style="color: #ddd; margin-bottom: 15px;"></i>
                        <h4 style="color: #666; margin-bottom: 10px;">Оборудование не найдено</h4>
                        <p>На этом энергоблоке еще нет зарегистрированного оборудования</p>
                        <a href="../equipment/add.php?block_id=<?php echo $block['id']; ?>" class="btn btn-success"
                            style="margin-top: 15px;">
                            <i class="fas fa-plus-circle"></i> Добавить оборудование
                        </a>
                    </div>
                <?php endif; ?>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="../equipment/add.php?block_id=<?php echo $block['id']; ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Добавить новое оборудование
                    </a>
                </div>
            </div>

            <!-- Последние показания -->
            <?php if (mysqli_num_rows($measurements) > 0): ?>
                <div style="margin-top: 40px;">
                    <h3><i class="fas fa-chart-line"></i> Последние показания</h3>

                    <div class="table-container" style="margin-top: 20px;">
                        <table class="measurements-table">
                            <thead>
                                <tr>
                                    <th>Время</th>
                                    <th>Оборудование</th>
                                    <th>Параметр</th>
                                    <th>Значение</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($measurement = $measurements->fetch_assoc()): ?>
                                    <tr <?php if ($measurement['is_alarm'])
                                        echo 'style="background-color: #f8d7da;"'; ?>>
                                        <td>
                                            <?php echo date('H:i d.m', strtotime($measurement['measured_at'])); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($measurement['equipment_name']); ?></td>
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
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="../measurements/index.php?block=<?php echo $block['id']; ?>" class="btn">
                            <i class="fas fa-chart-bar"></i> Все показания блока
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="action-bar">
                <a href="index.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
                <a href="../equipment/add.php?block_id=<?php echo $block['id']; ?>" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Добавить оборудование
                </a>
                <a href="../measurements/add.php?block_id=<?php echo $block['id']; ?>" class="btn">
                    <i class="fas fa-plus"></i> Добавить показания
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

    <script>
        // Фильтрация оборудования по статусу
        function filterEquipment(status) {
            const items = document.querySelectorAll('.equipment-item');
            items.forEach(item => {
                if (status === 'all' || item.querySelector('.status').textContent.includes(status)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Обновление статистики в реальном времени
        function updateStats() {
            // Здесь можно добавить AJAX запрос для обновления статистики
            console.log('Статистика обновлена');
        }

        // Автообновление каждые 30 секунд
        setInterval(updateStats, 30000);
    </script>
</body>

</html>