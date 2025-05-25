<?php
namespace app\Router;

use app\Router\View;
use app\Controllers\AuthController;
use app\Controllers\ArticleController;

class Router
{
    //### PATTERNS ROUTER PAGE ###
    private static $patterns = [
        // GET routes
        '~^(?:PHP-APP/public/?)?$~' => [View::class, 'on_Main'],       // Главная страница
        '~^(?:PHP-APP/public/)?login/?$~' => [
            'GET' => [AuthController::class, 'showLogin'],
            'POST' => [AuthController::class, 'login']
        ],
        '~^(?:PHP-APP/public/)?register/?$~' => [
            'GET' => [AuthController::class, 'showRegister'],
            'POST' => [AuthController::class, 'register']
        ],
        '~^(?:PHP-APP/public/)?article/?$~' => [ArticleController::class, 'list'], // Список статей
        '~^(?:PHP-APP/public/)?article/create/?$~' => [ArticleController::class, 'create'], // Создание статьи
        '~^(?:PHP-APP/public/)?article/store/?$~' => [
            'POST' => [ArticleController::class, 'store']
        ],
        '~^(?:PHP-APP/public/)?article/edit/(\d+)/?$~' => [ArticleController::class, 'showEditForm'], // Редактирование статьи
        '~^(?:PHP-APP/public/)?article/update/(\d+)/?$~' => [
            'POST' => [ArticleController::class, 'update']
        ],
        '~^(?:PHP-APP/public/)?article/delete/(\d+)/?$~' => [
            'POST' => [ArticleController::class, 'delete']
        ],
        '~^(?:PHP-APP/public/)?logout/?$~' => [AuthController::class, 'logout'] // Выход
    ];
    public function onRoute()
    {
        self::onAutoloadRegister();

        // Получаем текущий маршрут из .htaccess
        if (isset($_GET['route'])) {
            $route = trim($_GET['route'], '/');
            error_log("Маршрут из _GET['route']: " . $route);
        } else {
            $route = trim($_SERVER['REQUEST_URI'], '/');
            error_log("Маршрут из REQUEST_URI: " . $route);
        }
        
        error_log("GET параметры: " . print_r($_GET, true));
        error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
        error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
        
        // Отладочная информация
        error_log("Текущий маршрут после обработки: '" . $route . "'");
        
        $findRoute = false;
        $method = $_SERVER['REQUEST_METHOD'];

        foreach (self::$patterns as $pattern => $handlers) {
            error_log("Проверяем паттерн: " . $pattern . " для маршрута: " . $route);
            if (preg_match($pattern, $route, $matches)) {
                error_log("Паттерн совпал!");
                $findRoute = true;
                unset($matches[0]);

                // Проверяем, есть ли обработчик для данного метода
                if (is_array($handlers) && isset($handlers[$method])) {
                    $handler = $handlers[$method];
                } else {
                    // Если handlers не массив, значит это простой GET-маршрут
                    $handler = $handlers;
                }

                $controller = new $handler[0];
                $action = $handler[1];
                $controller->$action(...$matches);
                break;
            }
        }
        if (!$findRoute) {
            error_log("Маршрут не найден для: " . $route);
            header("HTTP/1.1 404 Страница не найдена");
            (new Router())->error_404();
            exit();
        }
    }
    public static function onAutoloadRegister(): void {
        spl_autoload_register(function ($className) {
            $filePath = dirname(__DIR__) . '/' . str_replace(['\\', 'app\Models'], ['/', ''], $className) . '.php';

            if (file_exists($filePath)) {
                require_once $filePath;
            } else {
               error_log("Ошибка загрузки класса '$className'. Файл не существует по пути: $filePath");
            }
        });
    }
    //### ROUTES PAGE ###

    public static function error_404()
    {
        include dirname(__DIR__, 1) . '/Views/404.php';
        exit();
    }
}
?>
