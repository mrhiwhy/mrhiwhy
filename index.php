<?php
// index.php - Главная страница (с проверкой авторизации)
require_once 'config.php';
check_auth(); // Проверяем авторизацию

// Получаем статистику
$stats = [
    'personnel' => mysqli_query($conn, "SELECT COUNT(*) as count FROM personnel WHERE status = 'Работает'")->fetch_assoc()['count'],
    'blocks' => mysqli_query($conn, "SELECT COUNT(*) as count FROM energy_blocks WHERE status = 'В работе'")->fetch_assoc()['count'],
    'equipment' => mysqli_query($conn, "SELECT COUNT(*) as count FROM equipment WHERE equipment_status = 'Исправен'")->fetch_assoc()['count'],
    'alarms' => mysqli_query($conn, "SELECT COUNT(*) as count FROM measurements WHERE is_alarm = TRUE AND measured_at >= NOW() - INTERVAL 24 HOUR")->fetch_assoc()['count']
];

// Последние показания
$recent_measurements = mysqli_query($conn, 
    "SELECT m.*, e.equipment_name, e.equipment_type 
     FROM measurements m 
     JOIN equipment e ON m.equipment_id = e.id 
     ORDER BY m.measured_at DESC 
     LIMIT 5");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Беловская ГРЭС - Панель управления</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-industry fa-2x"></i>
                <div>
                    <h1>Беловская ГРЭС</h1>
                    <span>Добро пожаловать, <?php echo $_SESSION['username']; ?>!</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="personnel/index.php"><i class="fas fa-users"></i> Персонал</a></li>
                    <li><a href="blocks/index.php"><i class="fas fa-bolt"></i> Энергоблоки</a></li>
                    <li><a href="equipment/index.php"><i class="fas fa-cogs"></i> Оборудование</a></li>
                    <li><a href="measurements/index.php"><i class="fas fa-chart-line"></i> Показания</a></li>
                    <li><a href="logout.php" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-tachometer-alt"></i> Панель управления</h2>
            <div>Сегодня: <?php echo date('d.m.Y'); ?></div>
        </div>

        <!-- Статистика -->
        <div class="cards-container">
            <div class="card">
                <h3><i class="fas fa-users text-blue"></i> Персонал</h3>
                <div class="card-stat"><?php echo $stats['personnel']; ?></div>
                <p>Сотрудников работают</p>
                <a href="personnel/index.php" class="btn">Подробнее</a>
            </div>

            <div class="card">
                <h3><i class="fas fa-bolt text-green"></i> Энергоблоки</h3>
                <div class="card-stat"><?php echo $stats['blocks']; ?></div>
                <p>Блоков в работе</p>
                <a href="blocks/index.php" class="btn">Подробнее</a>
            </div>

            <div class="card">
                <h3><i class="fas fa-cogs text-orange"></i> Оборудование</h3>
                <div class="card-stat"><?php echo $stats['equipment']; ?></div>
                <p>Единиц исправно</p>
                <a href="equipment/index.php" class="btn">Подробнее</a>
            </div>

            <div class="card">
                <h3><i class="fas fa-exclamation-triangle text-red"></i> Аварии</h3>
                <div class="card-stat"><?php echo $stats['alarms']; ?></div>
                <p>За последние 24 часа</p>
                <a href="measurements/alarms.php" class="btn">Подробнее</a>
            </div>
        </div>

        <!-- Последние показания -->
        <div style="margin-top: 40px;">
            <div class="page-header">
                <h3><i class="fas fa-chart-line"></i> Последние показания</h3>
                <a href="measurements/index.php" class="btn">Все показания</a>
            </div>
            
            <div class="table-container">
                <table>
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
                        <?php while($row = $recent_measurements->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('H:i d.m.Y', strtotime($row['measured_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['equipment_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['parameter_name']); ?></td>
                            <td><strong><?php echo $row['value']; ?> <?php echo htmlspecialchars($row['unit']); ?></strong></td>
                            <td>
                                <?php if($row['is_alarm']): ?>
                                    <span class="status status-repair">Авария</span>
                                <?php else: ?>
                                    <span class="status status-working">Норма</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div style="margin-top: 40px;">
            <h3><i class="fas fa-rocket"></i> Быстрые действия</h3>
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <a href="personnel/add.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Добавить сотрудника</a>
                <a href="equipment/add.php" class="btn btn-warning"><i class="fas fa-cog"></i> Добавить оборудование</a>
                <a href="measurements/add.php" class="btn"><i class="fas fa-plus-circle"></i> Добавить показания</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?>. Все права защищены.</p>
            <p>Версия системы: 1.0.0 | Последнее обновление: 18.12.2024</p>
        </div>
    </footer>

    <script>
        // Автообновление каждые 60 секунд
        setTimeout(function() {
            window.location.reload();
        }, 60000);
    </script>
</body>
</html>