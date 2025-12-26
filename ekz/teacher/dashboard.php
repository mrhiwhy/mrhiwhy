<?php
require_once '../config/database.php';
if(!isTeacher() && !isLoggedIn()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Получаем список студентов
$query = "SELECT * FROM users WHERE role = 'student' AND is_active = TRUE";
$stmt = $db->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Добавление оценки
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_grade'])) {
    $student_id = $_POST['student_id'];
    $subject_name = $_POST['subject_name'];
    $grade = $_POST['grade'];
    $comment = $_POST['comment'];
    
    $query = "INSERT INTO grades (student_id, subject_name, grade, date, teacher_id, comment) 
              VALUES (:student_id, :subject_name, :grade, CURDATE(), :teacher_id, :comment)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':subject_name', $subject_name);
    $stmt->bindParam(':grade', $grade);
    $stmt->bindParam(':teacher_id', $user_id);
    $stmt->bindParam(':comment', $comment);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет преподавателя</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-warning">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Кабинет преподавателя</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-item nav-link" href="students.php">Студенты</a>
                <a class="nav-item nav-link" href="grades.php">Оценки</a>
                <a class="nav-item nav-link" href="schedule.php">Расписание</a>
                <a class="nav-item nav-link" href="../index.php">Главная</a>
                <a class="nav-item nav-link" href="../logout.php">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <!-- Форма добавления оценки -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Добавить оценку</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Студент</label>
                                    <select name="student_id" class="form-select" required>
                                        <option value="">Выберите студента</option>
                                        <?php foreach($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>">
                                                <?php echo $student['full_name'] . ' (' . $student['group_name'] . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Предмет</label>
                                    <input type="text" name="subject_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Оценка</label>
                                    <select name="grade" class="form-select" required>
                                        <option value="5">5 (Отлично)</option>
                                        <option value="4">4 (Хорошо)</option>
                                        <option value="3">3 (Удовлетворительно)</option>
                                        <option value="2">2 (Неудовлетворительно)</option>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label>Комментарий</label>
                                    <input type="text" name="comment" class="form-control">
                                </div>
                            </div>
                            <button type="submit" name="add_grade" class="btn btn-primary">Добавить оценку</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Статистика -->
                <div class="card">
                    <div class="card-header">
                        <h5>Статистика</h5>
                    </div>
                    <div class="card-body">
                        <p>Всего студентов: <?php echo count($students); ?></p>
                        <p>Средний балл группы: 4.2</p>
                        <p>Лучший студент: Петров П.П.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>