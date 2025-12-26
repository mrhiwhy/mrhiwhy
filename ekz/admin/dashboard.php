<?php
require_once '../config/database.php';
if(!isAdmin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Статистика
$query = "SELECT COUNT(*) as count, role FROM users GROUP BY role";
$stmt = $db->prepare($query);
$stmt->execute();
$user_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем всех пользователей
$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Управление пользователями
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    
    switch($action) {
        case 'delete':
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            break;
        case 'toggle_active':
            $query = "UPDATE users SET is_active = NOT is_active WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            break;
    }
    redirect('dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Админ-панель</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="users.php"><i class="bi bi-people"></i> Пользователи</a>
                <a class="nav-link" href="news.php"><i class="bi bi-newspaper"></i> Новости</a>
                <a class="nav-link" href="schedule.php"><i class="bi bi-calendar"></i> Расписание</a>
                <a class="nav-link" href="reports.php"><i class="bi bi-graph-up"></i> Отчеты</a>
                <a class="nav-link" href="../index.php"><i class="bi bi-house"></i> Сайт</a>
                <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Статистика -->
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Всего пользователей</h5>
                                <h2><?php echo count($users); ?></h2>
                            </div>
                            <i class="bi bi-people" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Студенты</h5>
                                <h2>
                                    <?php 
                                    $count = 0;
                                    foreach($user_stats as $stat) {
                                        if($stat['role'] == 'student') {
                                            $count = $stat['count'];
                                            break;
                                        }
                                    }
                                    echo $count;
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-mortarboard" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Преподаватели</h5>
                                <h2>
                                    <?php 
                                    $count = 0;
                                    foreach($user_stats as $stat) {
                                        if($stat['role'] == 'teacher') {
                                            $count = $stat['count'];
                                            break;
                                        }
                                    }
                                    echo $count;
                                    ?>
                                </h2>
                            </div>
                            <i class="bi bi-person-badge" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5>Новых за месяц</h5>
                                <h2>12</h2>
                            </div>
                            <i class="bi bi-plus-circle" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Таблица пользователей -->
        <div class="card">
            <div class="card-header">
                <h5>Управление пользователями</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ФИО</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th>Группа</th>
                                <th>Статус</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['full_name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($user['role']) {
                                            case 'admin': echo 'danger'; break;
                                            case 'teacher': echo 'warning'; break;
                                            case 'student': echo 'success'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </td>
                                <td><?php echo $user['group_name']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $user['is_active'] ? 'Активен' : 'Неактивен'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?action=toggle_active&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-outline-warning">
                                            <i class="bi bi-power"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('Удалить пользователя?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>