<?php
// personnel/view.php
require_once '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM personnel WHERE id = $id";
$result = mysqli_query($conn, $sql);
$personnel = mysqli_fetch_assoc($result);

if (!$personnel) {
    header("Location: index.php?error=notfound");
    exit();
}

// Рассчитываем стаж
$hire_date = new DateTime($personnel['hire_date']);
$today = new DateTime();
$experience = $hire_date->diff($today);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр сотрудника - Беловская ГРЭС</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px solid #e1e5eb;
        }

        .avatar-large {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            flex-shrink: 0;
        }

        .profile-title h2 {
            margin: 0;
            color: #2c3e50;
        }

        .profile-title .badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .info-card h4 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .info-card p {
            margin: 5px 0;
            color: #555;
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
                    <span>Карточка сотрудника</span>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php"><i class="fas fa-home"></i> Главная</a></li>
                    <li><a href="index.php"><i class="fas fa-users"></i> Персонал</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container fade-in">
        <div class="page-header">
            <h2><i class="fas fa-user-circle"></i> Карточка сотрудника</h2>
            <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Назад к списку</a>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-large">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-title">
                    <h2><?php echo htmlspecialchars($personnel['full_name']); ?></h2>
                    <div class="badge" style="background: 
                        <?php
                        if ($personnel['category'] == 'Руководитель')
                            echo '#3498db';
                        elseif ($personnel['category'] == 'Специалист')
                            echo '#2ecc71';
                        else
                            echo '#f39c12';
                        ?>; 
                        color: white;">
                        <?php echo htmlspecialchars($personnel['category']); ?>
                    </div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h4><i class="fas fa-id-card"></i> Основная информация</h4>
                    <p><strong>Табельный номер:</strong> <?php echo htmlspecialchars($personnel['personnel_number']); ?>
                    </p>
                    <p><strong>Должность:</strong> <?php echo htmlspecialchars($personnel['position']); ?></p>
                    <p><strong>Отдел:</strong> <?php echo htmlspecialchars($personnel['department']); ?></p>
                </div>

                <div class="info-card">
                    <h4><i class="fas fa-calendar-alt"></i> Работа</h4>
                    <p><strong>Дата приема:</strong> <?php echo date('d.m.Y', strtotime($personnel['hire_date'])); ?>
                    </p>
                    <p><strong>Стаж:</strong> <?php echo $experience->y; ?> лет <?php echo $experience->m; ?> мес.</p>
                    <p><strong>Статус:</strong>
                        <span class="status <?php
                        if ($personnel['status'] == 'Работает')
                            echo 'status-working';
                        elseif ($personnel['status'] == 'В отпуске')
                            echo 'status-vacation';
                        else
                            echo 'status-repair';
                        ?>">
                            <?php echo htmlspecialchars($personnel['status']); ?>
                        </span>
                    </p>
                </div>

                <div class="info-card">
                    <h4><i class="fas fa-address-book"></i> Контакты</h4>
                    <p><strong>Телефон:</strong>
                        <?php echo $personnel['phone'] ? htmlspecialchars($personnel['phone']) : '—'; ?></p>
                    <p><strong>Email:</strong>
                        <?php echo $personnel['email'] ? htmlspecialchars($personnel['email']) : '—'; ?></p>
                </div>

                <div class="info-card">
                    <h4><i class="fas fa-info-circle"></i> Дополнительно</h4>
                    <p><strong>Последнее изменение:</strong><br>
                        <?php
                        if (isset($personnel['updated_at'])) {
                            echo date('d.m.Y H:i', strtotime($personnel['updated_at']));
                        } else {
                            echo '—';
                        }
                        ?>
                    </p>
                </div>
            </div>

            <div class="action-bar">
                <a href="edit.php?id=<?php echo $personnel['id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
                <a href="index.php?delete=<?php echo $personnel['id']; ?>" class="btn btn-danger"
                    onclick="return confirm('Удалить сотрудника <?php echo addslashes($personnel['full_name']); ?>?')">
                    <i class="fas fa-trash"></i> Удалить
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