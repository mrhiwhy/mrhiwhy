<?php
// equipment/index.php
require_once '../config.php';

// Получаем список оборудования с информацией о блоке
$sql = "SELECT e.*, eb.block_number, eb.block_name 
        FROM equipment e 
        JOIN energy_blocks eb ON e.block_id = eb.id 
        ORDER BY e.equipment_type, e.equipment_name";
$result = mysqli_query($conn, $sql);

// Удаление оборудования
if (isset($_GET['delete'])) {
    $id = safe_input($_GET['delete']);
    $delete_sql = "DELETE FROM equipment WHERE id = $id";
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: index.php?message=deleted");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оборудование - Беловская ГРЭС</title>
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
                    <span>Управление оборудованием</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="../personnel/index.php"><i class="fas fa-users"></i> Персонал</a></li>
                    <li><a href="../blocks/index.php"><i class="fas fa-bolt"></i> Энергоблоки</a></li>
                    <li><a href="index.php" class="active"><i class="fas fa-cogs"></i> Оборудование</a></li>
                    <li><a href="../measurements/index.php"><i class="fas fa-chart-line"></i> Показания</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-cogs"></i> Оборудование станции</h2>
            <div>
                <a href="add.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> Добавить</a>
            </div>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <?php
                if ($_GET['message'] == 'added')
                    echo "✅ Оборудование успешно добавлено!";
                if ($_GET['message'] == 'updated')
                    echo "✅ Данные оборудования обновлены!";
                if ($_GET['message'] == 'deleted')
                    echo "✅ Оборудование удалено!";
                ?>
            </div>
        <?php endif; ?>

        <!-- Фильтры -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <input type="text" id="searchInput" placeholder="Поиск оборудования..."
                    style="flex: 1; min-width: 200px; padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px;">
                <button onclick="filterTable()" class="btn">
                    <i class="fas fa-search"></i> Поиск
                </button>
                <button onclick="resetFilters()" class="btn">
                    <i class="fas fa-redo"></i> Сбросить
                </button>
            </div>
        </div>

        <!-- Таблица оборудования -->
        <div class="table-container">
            <table id="equipmentTable">
                <thead>
                    <tr>
                        <th>Код</th>
                        <th>Наименование</th>
                        <th>Тип</th>
                        <th>Энергоблок</th>
                        <th>Состояние</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $status_class = 'status-working';
                        if ($row['equipment_status'] == 'Требует ремонта')
                            $status_class = 'status-vacation';
                        elseif ($row['equipment_status'] == 'В ремонте')
                            $status_class = 'status-repair';
                        elseif ($row['equipment_status'] == 'Списано')
                            $status_class = 'status-repair';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['equipment_code']); ?></strong></td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($row['equipment_name']); ?></div>
                                <?php if ($row['manufacturer']): ?>
                                    <div style="font-size: 0.85rem; color: #666;">
                                        <?php echo htmlspecialchars($row['manufacturer']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span
                                    style="background: #e1f5fe; padding: 4px 10px; border-radius: 4px; font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($row['equipment_type']); ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($row['block_number']); ?></div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <?php echo htmlspecialchars($row['block_name']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="status <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($row['equipment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn-action btn-view"
                                        title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit"
                                        title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="index.php?delete=<?php echo $row['id']; ?>" class="btn-action btn-delete"
                                        title="Удалить"
                                        onclick="return confirm('Удалить оборудование <?php echo addslashes($row['equipment_name']); ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Статистика -->
        <div style="margin-top: 40px;">
            <div class="cards-container">
                <?php
                // Статистика по типам оборудования
                $type_stats = mysqli_query(
                    $conn,
                    "SELECT equipment_type, COUNT(*) as count,
                            SUM(CASE WHEN equipment_status = 'Исправен' THEN 1 ELSE 0 END) as working
                     FROM equipment 
                     GROUP BY equipment_type"
                );

                while ($stat = $type_stats->fetch_assoc()):
                    $percentage = $stat['count'] > 0
                        ? round(($stat['working'] / $stat['count']) * 100, 1)
                        : 0;
                    ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($stat['equipment_type']); ?></h3>
                        <div class="card-stat"><?php echo $stat['count']; ?></div>
                        <p>единиц оборудования</p>
                        <div style="margin-top: 10px; font-size: 0.9rem;">
                            <span style="color: <?php echo $percentage > 80 ? '#27ae60' : '#f39c12'; ?>;">
                                <i class="fas fa-check-circle"></i> <?php echo $stat['working']; ?> исправно
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <style>
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-view {
            background-color: #3498db;
            color: white;
        }

        .btn-edit {
            background-color: #f39c12;
            color: white;
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-view:hover {
            background-color: #2980b9;
        }

        .btn-edit:hover {
            background-color: #e67e22;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }
    </style>

    <script>
        // Функция поиска в таблице
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('equipmentTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            const rows = document.querySelectorAll('#equipmentTable tr');
            rows.forEach(row => row.style.display = '');
        }

        // Автоматический поиск при вводе
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
    </script>
</body>

</html>