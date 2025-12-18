<?php
// config.php (расширенная версия)
session_start();

// Учетные данные Nadutkin R0l_4c
define('DB_HOST', 'localhost');
define('DB_USER', 'Nadutkin');
define('DB_PASS', 'R0l_4c');
define('DB_NAME', 'belovskaya_gres');

// Создаем подключение
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Функция для безопасности ввода
function safe_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Функция проверки авторизации с ролями
function check_auth($required_role = null) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    if($required_role && $_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'admin') {
        die("Недостаточно прав для доступа к этой странице");
    }
}

// Функция для проверки логина и пароля
function verify_login($username, $password) {
    global $conn;
    
    $username = safe_input($username);
    $sql = "SELECT * FROM system_users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if(password_verify($password, $user['password_hash'])) {
            // Обновляем время последнего входа
            $update_sql = "UPDATE system_users SET last_login = NOW() WHERE id = " . $user['id'];
            mysqli_query($conn, $update_sql);
            
            return $user;
        }
    }
    
    return false;
}
?>