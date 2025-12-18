<?php
// measurements/index.php
require_once '../config.php';

// Получаем последние показания
$sql = "SELECT m.*, e.equipment_name, e.equipment_type, eb.block_number 
        FROM measurements m 
        JOIN equipment e ON m.equipment_id = e.id 
        JOIN energy_blocks eb ON e.block_id = eb.id 
        ORDER BY m.measured_at DESC 
        LIMIT 50";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Показания - Беловская ГРЭС</title>
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
                    <span>Телеметрия и показания</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="../personnel/index.php"><i class="fas fa-users"></i> Персонал</a></li>
                    <li><a href="../blocks/index.php"><i class="fas fa-bolt"></i> Энергоблоки</a></li>
                    <li><a href="../equipment/index.php"><i class="fas fa-cogs"></i> Оборудование</a></li>
                    <li><a href="index.php" class="active"><i class="fas fa-chart-line"></i> Показания</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-chart-line"></i> Телеметрия оборудования</h2>
            <div>
                <a href="alarms.php" class="btn btn-danger" style="margin-right: 15px;">
                    <i class="fas fa-exclamation-triangle"></i> Аварийные
                </a>
                <a href="add.php" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Добавить показания
                </a>
            </div>
        </div>

        <!-- Фильтры -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Дата с</label>
                    <input type="date" style="width: 100%; padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px;" 
                           value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Дата по</label>
                    <input type="date" style="width: 100%; padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px;" 
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div style="flex: 2; min-width: 200px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Оборудование</label>
                    <select style="width: 100%; padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px;">
                        <option>Все оборудование</option>
                        <?php
                        $equipment_list = mysqli_query($conn, "SELECT id, equipment_name FROM equipment ORDER BY equipment_name");
                        while($eq = $equipment_list->fetch_assoc()):
                        ?>
                        <option value="<?php echo $eq['id']; ?>"><?php echo htmlspecialchars($eq['equipment_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="align-self: flex-end;">
                    <button class="btn"><i class="fas fa-filter"></i> Применить</button>
                </div>
            </div>
        </div>

        <!-- Таблица показаний -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Время</th>
                        <th>Энергоблок</th>
                        <th>Оборудование</th>
                        <th>Параметр</th>
                        <th>Значение</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): 
                        $is_alarm = $row['is_alarm'];
                    ?>
                    <tr <?php if($is_alarm) echo 'style="background-color: #f8d7da;"'; ?>>
                        <td>
                            <div style="font-size: 0.85rem; color: #666;">
                                <?php echo date('d.m.Y', strtotime($row['measured_at'])); ?>
                            </div>
                            <div style="font-weight: 500;">
                                <?php echo date('H:i:s', strtotime($row['measured_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <span style="background: #e1f5fe; padding: 3px 8px; border-radius: 4px; font-size: 0.85rem;">
                                <?php echo htmlspecialchars($row['block_number']); ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($row['equipment_name']); ?></div>
                            <div style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($row['equipment_type']); ?></div>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($row['parameter_name']); ?></div>
                            <div style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($row['parameter_type']); ?></div>
                        </td>
                        <td>
                            <div style="font-size: 1.2rem; font-weight: bold; color: <?php echo $is_alarm ? '#c0392b' : '#2c3e50'; ?>;">
                                <?php echo $row['value']; ?>
                            </div>
                            <div style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars($row['unit']); ?></div>
                        </td>
                        <td>
                            <?php if($is_alarm): ?>
                                <span class="status status-repair">
                                    <i class="fas fa-exclamation-triangle"></i> Авария
                                </span>
                            <?php else: ?>
                                <span class="status status-working">Норма</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Статистика по параметрам -->
        <div style="margin-top: 40px;">
            <h3><i class="fas fa-chart-bar"></i> Статистика по типам параметров</h3>
            
            <?php
            $param_stats = mysqli_query($conn,
                "SELECT parameter_type, 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_alarm = TRUE THEN 1 ELSE 0 END) as alarms,
                        AVG(value) as avg_value
                 FROM measurements 
                 WHERE measured_at >= NOW() - INTERVAL 24 HOUR 
                 GROUP BY parameter_type 
                 ORDER BY parameter_type");
            ?>
            
            <div class="cards-container" style="margin-top: 20px;">
                <?php while($stat = $param_stats->fetch_assoc()): 
                    $alarm_percentage = $stat['total'] > 0 ? round(($stat['alarms'] / $stat['total']) * 100, 1) : 0;
                ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($stat['parameter_type']); ?></h3>
                    <div class="card-stat"><?php echo number_format($stat['avg_value'], 2); ?></div>
                    <p>среднее значение</p>
                    <div style="margin-top: 15px;">
                        <div style="font-size: 0.9rem;">
                            Всего показаний: <strong><?php echo $stat['total']; ?></strong>
                        </div>
                        <div style="font-size: 0.9rem;">
                            Аварийных: <strong><?php echo $stat['alarms']; ?></strong>
                            (<?php echo $alarm_percentage; ?>%)
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?></p>
            <p style="font-size: 0.8rem; margin-top: 10px; color: rgba(255,255,255,0.7);">
                Данные обновляются автоматически каждую минуту
            </p>
        </div>
    </footer>

    <script>
        // Автообновление страницы каждые 60 секунд
        setTimeout(function() {
            window.location.reload();
        }, 60000);
        
        // Подсветка аварийных строк
        document.addEventListener('DOMContentLoaded', function() {
            const alarmRows = document.querySelectorAll('tr[style*="background-color: #f8d7da"]');
            alarmRows.forEach(row => {
                row.addEventListener('click', function() {
                    alert('Аварийное значение! Проверьте оборудование.');
                });
            });
        });
    </script>
</body>
</html>