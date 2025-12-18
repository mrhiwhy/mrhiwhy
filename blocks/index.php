<?php
// blocks/index.php
require_once '../config.php';

// Получаем список энергоблоков с информацией об ответственных
$sql = "SELECT eb.*, p.full_name as responsible_name 
        FROM energy_blocks eb 
        LEFT JOIN personnel p ON eb.responsible_personnel_id = p.id 
        ORDER BY eb.block_number";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Энергоблоки - Беловская ГРЭС</title>
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
                    <span>Управление энергоблоками</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="../personnel/index.php"><i class="fas fa-users"></i> Персонал</a></li>
                    <li><a href="index.php" class="active"><i class="fas fa-bolt"></i> Энергоблоки</a></li>
                    <li><a href="../equipment/index.php"><i class="fas fa-cogs"></i> Оборудование</a></li>
                    <li><a href="../measurements/index.php"><i class="fas fa-chart-line"></i> Показания</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-bolt"></i> Энергоблоки станции</h2>
            <div>
                <span style="margin-right: 20px;">
                    <i class="fas fa-chart-bar"></i> Общая мощность: 
                    <?php 
                    $total_power = mysqli_query($conn, 
                        "SELECT SUM(power_mw) as total FROM energy_blocks WHERE status = 'В работе'")->fetch_assoc()['total'];
                    echo number_format($total_power, 1); ?> МВт
                </span>
            </div>
        </div>

        <div class="cards-container">
            <?php while($block = $result->fetch_assoc()): 
                $status_class = 'status-working';
                if($block['status'] == 'В ремонте') $status_class = 'status-repair';
                elseif($block['status'] == 'В резерве') $status_class = 'status-vacation';
            ?>
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <h3><?php echo htmlspecialchars($block['block_number']); ?></h3>
                    <span class="status <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($block['status']); ?>
                    </span>
                </div>
                
                <p style="margin: 10px 0; color: #666;">
                    <?php echo htmlspecialchars($block['block_name']); ?>
                </p>
                
                <div style="margin: 15px 0;">
                    <div style="font-size: 2rem; font-weight: bold; color: #3498db;">
                        <?php echo $block['power_mw']; ?> МВт
                    </div>
                    <div style="font-size: 0.9rem; color: #777;">
                        <?php echo htmlspecialchars($block['block_type']); ?> • Год ввода: <?php echo $block['commission_year']; ?>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                    <div style="font-size: 0.9rem;">
                        <strong>Ответственный:</strong><br>
                        <?php echo $block['responsible_name'] ? htmlspecialchars($block['responsible_name']) : 'Не назначен'; ?>
                    </div>
                </div>
                
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <a href="view.php?id=<?php echo $block['id']; ?>" class="btn" style="flex: 1;">
                        <i class="fas fa-eye"></i> Просмотр
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Статистика -->
        <div style="margin-top: 40px; background: white; padding: 25px; border-radius: 8px;">
            <h3><i class="fas fa-chart-pie"></i> Статистика энергоблоков</h3>
            
            <?php
            $stats = mysqli_query($conn, 
                "SELECT status, COUNT(*) as count, SUM(power_mw) as power 
                 FROM energy_blocks 
                 GROUP BY status");
            ?>
            
            <div style="display: flex; gap: 30px; margin-top: 20px; flex-wrap: wrap;">
                <?php while($stat = $stats->fetch_assoc()): ?>
                <div style="text-align: center; min-width: 150px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #3498db;">
                        <?php echo $stat['count']; ?>
                    </div>
                    <div style="font-size: 0.9rem; color: #777;">
                        <?php echo htmlspecialchars($stat['status']); ?>
                    </div>
                    <div style="font-size: 1.1rem; font-weight: 500; margin-top: 5px;">
                        <?php echo number_format($stat['power'], 1); ?> МВт
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Оборудование в работе -->
        <div style="margin-top: 40px;">
            <h3><i class="fas fa-cogs"></i> Оборудование по блокам</h3>
            
            <?php
            $equipment_by_block = mysqli_query($conn, 
                "SELECT eb.block_number, 
                        COUNT(e.id) as total_equipment,
                        SUM(CASE WHEN e.equipment_status = 'Исправен' THEN 1 ELSE 0 END) as working_equipment
                 FROM energy_blocks eb
                 LEFT JOIN equipment e ON eb.id = e.block_id
                 GROUP BY eb.id, eb.block_number
                 ORDER BY eb.block_number");
            ?>
            
            <div class="table-container" style="margin-top: 20px;">
                <table>
                    <thead>
                        <tr>
                            <th>Энергоблок</th>
                            <th>Всего оборудования</th>
                            <th>Исправно</th>
                            <th>Процент исправности</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $equipment_by_block->fetch_assoc()): 
                            $percentage = $row['total_equipment'] > 0 
                                ? round(($row['working_equipment'] / $row['total_equipment']) * 100, 1) 
                                : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['block_number']); ?></strong></td>
                            <td><?php echo $row['total_equipment']; ?></td>
                            <td><?php echo $row['working_equipment']; ?></td>
                            <td>
                                <div style="background: #e1e5eb; height: 20px; border-radius: 10px; overflow: hidden;">
                                    <div style="background: <?php 
                                        echo $percentage > 80 ? '#27ae60' : ($percentage > 50 ? '#f39c12' : '#e74c3c'); 
                                    ?>; 
                                    height: 100%; width: <?php echo $percentage; ?>%;"></div>
                                </div>
                                <span style="font-size: 0.9rem;"><?php echo $percentage; ?>%</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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