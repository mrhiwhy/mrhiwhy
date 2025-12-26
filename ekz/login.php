<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Поиск пользователя
    $query = "SELECT * FROM users WHERE username = :username OR email = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(password_verify($password, $user['password'])) {
            // Успешный вход
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Обновляем время последнего входа
            $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();
            
            // Перенаправляем в зависимости от роли
            switch($user['role']) {
                case 'admin':
                    redirect('admin/dashboard.php');
                    break;
                case 'teacher':
                    redirect('teacher/dashboard.php');
                    break;
                default:
                    redirect('student/dashboard.php');
            }
        } else {
            $error = 'Неверный пароль';
        }
    } else {
        $error = 'Пользователь не найден';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Колледж СПО</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-center">Вход в систему</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Имя пользователя или Email *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Войти</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
                            <p><a href="index.php">Вернуться на главную</a></p>
                        </div>
                    </div>
                </div>
                
                <!-- Демо доступы -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6>Демо доступы:</h6>
                        <p><strong>Админ:</strong> admin / admin123</p>
                        <p><strong>Студент:</strong> student1 / student123</p>
                        <p><strong>Преподаватель:</strong> teacher1 / teacher123</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>