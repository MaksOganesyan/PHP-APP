<?php
namespace App\Routes;

use App\Controllers\AuthController;
use App\Controllers\ArticleController;
use App\Models\User;

class Router {
    private $routes = [];
    private $basePath = '/PHP-APP/public'; // Базовый путь для Apache

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Удаляем базовый путь из URI
        if (strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        $uri = $uri === '' ? '/' : $uri;

        $method = $_SERVER['REQUEST_METHOD'];

        // Разбираем URI на части
        $uriParts = explode('/', trim($uri, '/'));

        // Формируем ключ маршрута из первых двух частей URI
        $routePath = '/' . (isset($uriParts[0]) ? $uriParts[0] : '');
        if (isset($uriParts[1])) {
            $routePath .= '/' . $uriParts[1];
        }

        if (isset($this->routes[$method][$routePath])) {
            // Если есть третий сегмент, передаем его в callback как параметр
            if (isset($uriParts[2])) {
                call_user_func($this->routes[$method][$routePath], $uriParts[2]);
            } else {
                call_user_func($this->routes[$method][$routePath]);
            }
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
    }
}

// Создаем объекты контроллеров и модели
$router = new Router();
$authController = new AuthController();
$articleController = new ArticleController();
$userModel = new User();

// Главная страница
$router->get('/', function() use ($userModel) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        $user = $userModel->findById($_SESSION['user_id']);
        echo "Welcome to the Blog Framework, " . htmlspecialchars($user['username']) . "! 
            <a href='/PHP-APP/public/logout'>Logout</a> | 
            <a href='/PHP-APP/public/article/create'>Create Article</a> | 
            <a href='/PHP-APP/public/articles'>View Articles</a>";
    } else {
        echo "Welcome to the Blog Framework! 
            <a href='/PHP-APP/public/login'>Login</a> | 
            <a href='/PHP-APP/public/register'>Register</a> | 
            <a href='/PHP-APP/public/articles'>View Articles</a>";
    }
});

// Авторизация
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/logout', [$authController, 'logout']);

// Статьи
$router->get('/articles', [$articleController, 'list']);                  // Список статей
$router->get('/article/create', [$articleController, 'create']);          // Форма создания статьи
$router->post('/article/create', [$articleController, 'store']);          // Сохранение новой статьи
$router->get('/article/edit', [$articleController, 'showEditForm']);      // Форма редактирования с параметром id
$router->post('/article/update', [$articleController, 'update']);         // Обновление статьи с параметром id
$router->get('/article/delete', [$articleController, 'delete']);          // Удаление статьи с параметром id

$router->dispatch();
?>
