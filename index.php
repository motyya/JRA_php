<?php
// Включить отображение ошибок (убрать на продакшене)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Обработка API запросов
if (isset($_GET['page']) && strpos($_GET['page'], 'api/') === 0) {
    $apiFile = __DIR__ . '/' . $_GET['page'] . '.php';
    if (file_exists($apiFile)) {
        require_once $apiFile;
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
        exit;
    }
}

// Маршрутизация страниц
$page = $_GET['page'] ?? 'index';
$allowedPages = [
    'index' => 'pages/index.html',
    'races' => 'pages/races.html',
    'horses' => 'pages/horses.html',
    'race-entry' => 'pages/race-entry.html',
    'jockeys-directory' => 'pages/jockeys-directory.html',
    'login' => 'pages/login.html',
    'register' => 'pages/register.html',
    'user' => 'pages/user.html',
    'racecourses' => 'pages/racecourses.html',
    'training_centers' => 'pages/training_centers.html',
    'jockeys' => 'pages/jockeys.html',
    'glossary' => 'pages/glossary.html'
];

// Проверка существования файла
if (isset($allowedPages[$page]) && file_exists($allowedPages[$page])) {
    // Читаем HTML файл
    $html = file_get_contents($allowedPages[$page]);
    
    // Заменяем пути к JS файлам
    $html = str_replace('../js/', 'js/', $html);
    $html = str_replace('../css/', 'css/', $html);
    $html = str_replace('../images/', 'images/', $html);
    $html = str_replace('../pages/', '?page=', $html);
    $html = str_replace('href="', 'href="?page=', $html);
    
    // Исправляем ссылки на JS файлы
    $html = preg_replace('/href="(\w+\.html)"/', 'href="?page=$1"', $html);
    
    echo $html;
} else {
    // 404 страница
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The requested page '{$page}' does not exist.</p>";
    echo '<a href="?page=index">Go to Homepage</a>';
}
?>