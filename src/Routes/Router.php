<?php
namespace App\Routes;

use App\Controllers\AuthController;
use App\Controllers\ArticleController;  // <-- добавляем
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

        if (isset($this->routes[$method][$uri])) {
            call_user_func($this->routes[$method][$uri]);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
    }
}

$router = new Router();
$authController = new AuthController();
$articleController = new ArticleController(); // <-- создаём экземпляр
$userModel = new User();

// Главная страница
$router->get('/', function() use ($userModel) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

    if (isset($_SESSION['user_id'])) {
        $user = $userModel->findById($_SESSION['user_id']);
        echo "Welcome to the Blog Framework, " . htmlspecialchars($user['username']) . "! <a href='/PHP-APP/public/logout'>Logout</a>";
    } else {
        echo "Welcome to the Blog Framework! <a href='/PHP-APP/public/login'>Login</a> | <a href='/PHP-APP/public/register'>Register</a>";
    }
});

// Авторизация
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/logout', [$authController, 'logout']);

// Добавляем маршрут для создания статьи
$router->get('/create_article', [$articleController, 'create']);

$router->dispatch();
?>
