<?php
require_once '../config/database.php';
if(!isStudent() && !isLoggedIn()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$group_name = 'ТП-21'; // Здесь нужно получить из БД

// Получаем данные пользователя
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем оценки
$query = "SELECT * FROM grades WHERE student_id = :student_id ORDER BY date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $user_id);
$stmt->execute();
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем расписание
$query = "SELECT * FROM schedule WHERE group_name = :group_name ORDER BY day_of_week, lesson_number";
$stmt = $db->prepare($query);
$stmt->bindParam(':group_name', $group_name);
$stmt->execute();
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет студента</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Кабинет студента</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link"><?php echo $user['full_name']; ?></span>
                <a class="nav-item nav-link" href="../index.php">Главная</a>
                <a class="nav-item nav-link" href="../logout.php">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Боковая панель -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="<?php echo $user['avatar'] ?: 'https://via.placeholder.com/150'; ?>" 
                             class="rounded-circle mb-3" width="100" height="100">
                        <h5><?php echo $user['full_name']; ?></h5>
                        <p class="text-muted">Студент</p>
                        <p>Группа: <?php echo $user['group_name']; ?></p>
                        <p>Специальность: <?php echo $user['specialty']; ?></p>
                        <p>Курс: <?php echo $user['course']; ?></p>
                    </div>
                </div>
                
                <div class="list-group mb-4">
                    <a href="#profile" class="list-group-item list-group-item-action active">Профиль</a>
                    <a href="#grades" class="list-group-item list-group-item-action">Успеваемость</a>
                    <a href="#schedule" class="list-group-item list-group-item-action">Расписание</a>
                    <a href="#news" class="list-group-item list-group-item-action">Новости</a>
                    <a href="#documents" class="list-group-item list-group-item-action">Документы</a>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="col-md-9">
                <!-- Успеваемость -->
                <div class="card mb-4" id="grades">
                    <div class="card-header">
                        <h5>Мои оценки</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Предмет</th>
                                        <th>Оценка</th>
                                        <th>Дата</th>
                                        <th>Преподаватель</th>
                                        <th>Комментарий</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($grades) > 0): ?>
                                        <?php foreach($grades as $grade): ?>
                                        <tr>
                                            <td><?php echo $grade['subject_name']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $grade['grade'] >= 4 ? 'success' : 
                                                         ($grade['grade'] == 3 ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo $grade['grade']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $grade['date']; ?></td>
                                            <td>Преподаватель</td>
                                            <td><?php echo $grade['comment']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Нет оценок</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Расписание -->
                <div class="card mb-4" id="schedule">
                    <div class="card-header">
                        <h5>Расписание занятий</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>День недели</th>
                                        <th>№ пары</th>
                                        <th>Предмет</th>
                                        <th>Аудитория</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($schedule) > 0): ?>
                                        <?php foreach($schedule as $lesson): ?>
                                        <tr>
                                            <td><?php echo $lesson['day_of_week']; ?></td>
                                            <td><?php echo $lesson['lesson_number']; ?></td>
                                            <td><?php echo $lesson['subject_name']; ?></td>
                                            <td><?php echo $lesson['classroom']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Расписание не загружено</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>