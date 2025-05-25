<?php
namespace app\Router;

use app\Router\View;//файл с переходником на страницы

class Router
{
    //### PATTERNS ROUTER PAGE ###
    private static $patterns = [
         '~^/?PHP-APP/public/?$~' => [View::class, 'on_Main'],      // http://localhost
    ];
    public function onRoute()
    {
        self::onAutoloadRegister();

        // Получаем текущий маршрут из .htaccess
        if (isset($_GET['route'])) {
            $route = trim($_GET['route'], '/');
        } else {
            $route = trim($_SERVER['REQUEST_URI'], '/');
        }
        $findRoute = false;

        foreach (self::$patterns as $pattern => $controllerAndAction) {
            if (preg_match($pattern, $route, $matches)) {
                $findRoute = true; // для выхода из цикла и подтверждения что маршрут найден
                unset($matches[0]);// удаляет первый элемент массива
                $action = $controllerAndAction[1]; // sayHello
                $controller = new $controllerAndAction[0];// App\Models\Page\Window
                $controller->$action(...$matches);
                break;
            }
        }
        if (!$findRoute) {
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
        include dirname(__DIR__, 1) . '/views/404.php';
        exit();
    }


}
?>
