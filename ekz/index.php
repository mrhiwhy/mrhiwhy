<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Колледж современных технологий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Колледж СПО</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Главная</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">О колледже</a></li>
                    <li class="nav-item"><a class="nav-link" href="#specialties">Специальности</a></li>
                    <li class="nav-item"><a class="nav-link" href="#news">Новости</a></li>
                    <?php if(isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Личный кабинет</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Выйти</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Вход</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Герой секция -->
    <header class="hero bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold">Колледж современных технологий</h1>
                    <p class="lead">Получи востребованную профессию и стань специалистом будущего</p>
                    <a href="register.php" class="btn btn-primary btn-lg">Поступить к нам</a>
                </div>
                <div class="col-lg-6">
                    <img src="images/college.jpg" alt="Колледж" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </header>

    <!-- О колледже -->
    <section id="about" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">О нашем колледже</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">Современное оборудование</h5>
                            <p class="card-text">Обучение на современном оборудовании и в цифровых лабораториях</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">Трудоустройство</h5>
                            <p class="card-text">100% трудоустройство выпускников по специальности</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">Практика</h5>
                            <p class="card-text">Производственная практика на ведущих предприятиях города</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Специальности -->
    <section id="specialties" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Наши специальности</h2>
            <div class="row">
                <?php
                $specialties = [
                    ['title' => 'Информационные системы', 'code' => '09.02.07'],
                    ['title' => 'Программирование', 'code' => '09.02.04'],
                    ['title' => 'Сетевое администрирование', 'code' => '09.02.02'],
                    ['title' => 'Экономика и бухучет', 'code' => '38.02.01'],
                    ['title' => 'Технология продукции', 'code' => '19.02.03'],
                    ['title' => 'Гостиничный сервис', 'code' => '43.02.11']
                ];
                
                foreach($specialties as $spec): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $spec['title']; ?></h5>
                            <p class="card-text">Код: <?php echo $spec['code']; ?></p>
                            <p>Срок обучения: 3 года 10 месяцев</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Новости -->
    <section id="news" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Последние новости</h2>
            <div class="row">
                <?php
                $query = "SELECT * FROM news ORDER BY created_at DESC LIMIT 3";
                $stmt = $db->prepare($query);
                $stmt->execute();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="card-text"><?php echo substr($row['content'], 0, 100) . '...'; ?></p>
                            <small class="text-muted"><?php echo date('d.m.Y', strtotime($row['created_at'])); ?></small>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Футер -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Контакты</h5>
                    <p>Адрес: г. Москва, ул. Образцова, 15</p>
                    <p>Телефон: +7 (495) 123-45-67</p>
                    <p>Email: info@college.ru</p>
                </div>
                <div class="col-md-6">
                    <h5>Приемная комиссия</h5>
                    <p>Работаем: Пн-Пт 9:00-18:00</p>
                    <p>Телефон: +7 (495) 765-43-21</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>