<?php
// personnel/index.php
require_once '../config.php';

// Получаем список персонала
$sql = "SELECT * FROM personnel ORDER BY department, position";
$result = mysqli_query($conn, $sql);

// Удаление сотрудника
if (isset($_GET['delete'])) {
    $id = safe_input($_GET['delete']);
    $delete_sql = "DELETE FROM personnel WHERE id = $id";
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
    <title>Персонал - Беловская ГРЭС</title>
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
                    <span>Управление персоналом</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="index.php" class="active"><i class="fas fa-users"></i> Персонал</a></li>
                    <li><a href="../blocks/index.php"><i class="fas fa-bolt"></i> Энергоблоки</a></li>
                    <li><a href="../equipment/index.php"><i class="fas fa-cogs"></i> Оборудование</a></li>
                    <li><a href="../measurements/index.php"><i class="fas fa-chart-line"></i> Показания</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-users"></i> Сотрудники Беловской ГРЭС</h2>
            <div>
                <a href="add.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Добавить</a>
                <a href="export.php" class="btn"><i class="fas fa-download"></i> Экспорт</a>
            </div>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <?php
                if ($_GET['message'] == 'added')
                    echo "✅ Сотрудник успешно добавлен!";
                if ($_GET['message'] == 'updated')
                    echo "✅ Данные сотрудника обновлены!";
                if ($_GET['message'] == 'deleted')
                    echo "✅ Сотрудник удален!";
                ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Таб. номер</th>
                        <th>ФИО</th>
                        <th>Должность</th>
                        <th>Отдел</th>
                        <th>Категория</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['personnel_number']); ?></strong></td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <?php echo htmlspecialchars($row['phone'] ?: '—'); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['position']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td>
                                <?php
                                $status_class = 'status-working';
                                if ($row['status'] == 'В отпуске')
                                    $status_class = 'status-vacation';
                                elseif ($row['status'] == 'На больничном')
                                    $status_class = 'status-repair';
                                elseif ($row['status'] == 'Уволен')
                                    $status_class = 'status-repair';
                                ?>
                                <span class="status <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
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
                                        onclick="return confirm('Удалить сотрудника <?php echo addslashes($row['full_name']); ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

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
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
</body>

</html>