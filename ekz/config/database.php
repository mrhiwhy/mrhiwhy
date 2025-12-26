<?php
session_start();

class Database {
    private $host = "localhost";
    private $db_name = "college_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                                 $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Автозагрузка классов
spl_autoload_register(function ($class_name) {
    include 'classes/' . $class_name . '.php';
});

// Проверка авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

function isTeacher() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'teacher';
}

function isStudent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'student';
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>