<?php
namespace app\Router;

use app\Router\View;//файл с переходником на страницы

class Router
{
    //### PATTERNS ROUTER PAGE ###
    private static $patterns = [
        '~^(?:PHP-APP/public/?)?$~' => [View::class, 'on_Main'],       // Главная страница
        '~^(?:PHP-APP/public/)?login/?$~' => [View::class, 'on_Login'], // Страница логина
        '~^(?:PHP-APP/public/)?register/?$~' => [View::class, 'on_Register'], // Страница регистрации
        '~^(?:PHP-APP/public/)?article/?$~' => [View::class, 'on_Article'], // Страница статьи
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

        foreach (self::$patterns as $pattern => $controllerAndAction) {
            error_log("Проверяем паттерн: " . $pattern . " для маршрута: " . $route);
            if (preg_match($pattern, $route, $matches)) {
                error_log("Паттерн совпал!");
                $findRoute = true;
                unset($matches[0]);
                $action = $controllerAndAction[1];
                $controller = new $controllerAndAction[0];
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
    public static function onAutoloadRegister(
    ): void {
        spl_autoload_register(function ($className) {

            $filePath = dirname(__DIR__) . '/' . str_replace(['\\', 'app\Models'], ['/', ''], $className) . '.php';

            if (file_exists($filePath)) {//have't file
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
