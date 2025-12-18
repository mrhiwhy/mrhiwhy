<?php
// personnel/edit.php
require_once '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Получаем данные сотрудника
$sql = "SELECT * FROM personnel WHERE id = $id";
$result = mysqli_query($conn, $sql);
$personnel = mysqli_fetch_assoc($result);

if (!$personnel) {
    header("Location: index.php?error=notfound");
    exit();
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $personnel_number = safe_input($_POST['personnel_number']);
    $full_name = safe_input($_POST['full_name']);
    $position = safe_input($_POST['position']);
    $department = safe_input($_POST['department']);
    $category = safe_input($_POST['category']);
    $hire_date = safe_input($_POST['hire_date']);
    $phone = safe_input($_POST['phone']);
    $email = safe_input($_POST['email']);
    $status = safe_input($_POST['status']);

    // Обновляем данные в БД
    $update_sql = "UPDATE personnel SET 
                    personnel_number = '$personnel_number',
                    full_name = '$full_name',
                    position = '$position',
                    department = '$department',
                    category = '$category',
                    hire_date = '$hire_date',
                    phone = '$phone',
                    email = '$email',
                    status = '$status',
                    updated_at = NOW()
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
    <title>Редактировать сотрудника - Беловская ГРЭС</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e1e5eb;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
        }

        .profile-info h3 {
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .profile-info p {
            color: #666;
            margin-bottom: 10px;
        }

        .form-actions {
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
                    <span>Редактирование сотрудника</span>
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
            <h2><i class="fas fa-user-edit"></i> Редактирование сотрудника</h2>
            <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Назад к списку</a>
        </div>

        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($personnel['full_name']); ?></h3>
                <p>Табельный номер: <strong><?php echo htmlspecialchars($personnel['personnel_number']); ?></strong></p>
                <p>Должность: <?php echo htmlspecialchars($personnel['position']); ?></p>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="personnel_number"><i class="fas fa-id-card"></i> Табельный номер *</label>
                        <input type="text" id="personnel_number" name="personnel_number"
                            value="<?php echo htmlspecialchars($personnel['personnel_number']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-user"></i> ФИО *</label>
                        <input type="text" id="full_name" name="full_name"
                            value="<?php echo htmlspecialchars($personnel['full_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="position"><i class="fas fa-briefcase"></i> Должность *</label>
                        <input type="text" id="position" name="position"
                            value="<?php echo htmlspecialchars($personnel['position']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="department"><i class="fas fa-building"></i> Отдел/Цех *</label>
                        <select id="department" name="department" required>
                            <option value="">Выберите отдел</option>
                            <?php
                            $departments = [
                                'Диспетчерская',
                                'Электроцех',
                                'Котельный цех',
                                'КИПиА',
                                'Химическая лаборатория',
                                'ПЭО',
                                'АХО',
                                'Ремонтный цех'
                            ];
                            foreach ($departments as $dept):
                                $selected = ($personnel['department'] == $dept) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $dept; ?>" <?php echo $selected; ?>>
                                    <?php echo $dept; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category"><i class="fas fa-layer-group"></i> Категория *</label>
                        <select id="category" name="category" required>
                            <?php
                            $categories = ['Рабочий', 'Специалист', 'Руководитель'];
                            foreach ($categories as $cat):
                                $selected = ($personnel['category'] == $cat) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $cat; ?>" <?php echo $selected; ?>>
                                    <?php echo $cat; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="hire_date"><i class="fas fa-calendar-alt"></i> Дата приема *</label>
                        <input type="date" id="hire_date" name="hire_date"
                            value="<?php echo htmlspecialchars($personnel['hire_date']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Телефон</label>
                        <input type="tel" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($personnel['phone']); ?>" placeholder="+79001234567">
                    </div>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($personnel['email']); ?>" placeholder="example@gres.ru">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status"><i class="fas fa-chart-line"></i> Статус *</label>
                    <select id="status" name="status" required>
                        <?php
                        $statuses = ['Работает', 'В отпуске', 'На больничном', 'Уволен'];
                        foreach ($statuses as $stat):
                            $selected = ($personnel['status'] == $stat) ? 'selected' : '';
                            ?>
                            <option value="<?php echo $stat; ?>" <?php echo $selected; ?>>
                                <?php echo $stat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success" style="flex: 1;">
                        <i class="fas fa-save"></i> Сохранить изменения
                    </button>
                    <a href="index.php" class="btn" style="flex: 1;">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>

        <!-- Быстрый просмотр -->
        <div style="margin-top: 40px; background: #f8f9fa; padding: 25px; border-radius: 8px;">
            <h3><i class="fas fa-history"></i> История изменений</h3>
            <div style="display: flex; gap: 30px; margin-top: 15px;">
                <div>
                    <strong>Дата приема:</strong><br>
                    <?php
                    $hire_date = new DateTime($personnel['hire_date']);
                    echo $hire_date->format('d.m.Y');
                    ?>
                </div>
                <div>
                    <strong>Стаж:</strong><br>
                    <?php
                    $today = new DateTime();
                    $interval = $hire_date->diff($today);
                    echo $interval->y . ' лет ' . $interval->m . ' мес.';
                    ?>
                </div>
                <div>
                    <strong>Последнее изменение:</strong><br>
                    <?php
                    if (isset($personnel['updated_at'])) {
                        echo date('d.m.Y H:i', strtotime($personnel['updated_at']));
                    } else {
                        echo '—';
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <script>
        // Автоматическое форматирование телефона
        document.getElementById('phone').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 1) value = '+7' + value.substring(1);
            if (value.length > 12) value = value.substring(0, 12);
            e.target.value = value;
        });

        // Подсказка для должности
        const positionSuggestions = [
            'Машинист котлов', 'Электромонтер', 'Инженер-наладчик',
            'Диспетчер', 'Лаборант', 'Слесарь', 'Экономист', 'Бухгалтер'
        ];

        document.getElementById('position').addEventListener('input', function (e) {
            const datalist = document.getElementById('position-suggestions') ||
                (function () {
                    const dl = document.createElement('datalist');
                    dl.id = 'position-suggestions';
                    positionSuggestions.forEach(pos => {
                        const option = document.createElement('option');
                        option.value = pos;
                        dl.appendChild(option);
                    });
                    document.body.appendChild(dl);
                    return dl;
                })();

            if (!this.list) {
                this.setAttribute('list', 'position-suggestions');
            }
        });
    </script>
</body>

</html>