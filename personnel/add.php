<?php
// personnel/add.php
require_once '../config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    
    // Вставляем данные в БД
    $sql = "INSERT INTO personnel (personnel_number, full_name, position, department, category, hire_date, phone, email, status) 
            VALUES ('$personnel_number', '$full_name', '$position', '$department', '$category', '$hire_date', '$phone', '$email', '$status')";
    
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
    <title>Добавить сотрудника - Беловская ГРЭС</title>
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
                    <span>Добавление сотрудника</span>
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
            <h2><i class="fas fa-user-plus"></i> Добавить нового сотрудника</h2>
            <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Назад к списку</a>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="personnel_number">Табельный номер *</label>
                        <input type="text" id="personnel_number" name="personnel_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">ФИО *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="position">Должность *</label>
                        <input type="text" id="position" name="position" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Отдел/Цех *</label>
                        <select id="department" name="department" required>
                            <option value="">Выберите отдел</option>
                            <option value="Диспетчерская">Диспетчерская</option>
                            <option value="Электроцех">Электроцех</option>
                            <option value="Котельный цех">Котельный цех</option>
                            <option value="КИПиА">КИПиА</option>
                            <option value="Химическая лаборатория">Химическая лаборатория</option>
                            <option value="ПЭО">ПЭО</option>
                            <option value="АХО">АХО</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Категория *</label>
                        <select id="category" name="category" required>
                            <option value="Рабочий">Рабочий</option>
                            <option value="Специалист">Специалист</option>
                            <option value="Руководитель">Руководитель</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="hire_date">Дата приема *</label>
                        <input type="date" id="hire_date" name="hire_date" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <input type="tel" id="phone" name="phone" placeholder="+79001234567">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="example@gres.ru">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Статус *</label>
                    <select id="status" name="status" required>
                        <option value="Работает">Работает</option>
                        <option value="В отпуске">В отпуске</option>
                        <option value="На больничном">На больничном</option>
                        <option value="Уволен">Уволен</option>
                    </select>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" class="btn btn-success" style="padding: 12px 40px;">
                        <i class="fas fa-save"></i> Сохранить сотрудника
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Беловская ГРЭС &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <script>
        // Установка сегодняшней даты по умолчанию
        document.getElementById('hire_date').valueAsDate = new Date();
        
        // Генерация табельного номера
        document.getElementById('personnel_number').addEventListener('focus', function() {
            if(!this.value) {
                const randomNum = Math.floor(Math.random() * 1000);
                this.value = 'П-' + randomNum.toString().padStart(3, '0');
            }
        });
    </script>
</body>
</html>