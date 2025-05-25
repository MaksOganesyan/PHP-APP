<?php
namespace app\Controllers;

class ArticleController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function list() {
        if (!isset($articles)) {
            // Временные тестовые данные
            $articles = [
                [
                    'article_id' => 1,
                    'title' => 'Test Article',
                    'content' => 'Test Content',
                    'username' => 'Test User',
                    'category_name' => 'Test Category',
                    'status' => 'published',
                    'created_at' => date('Y-m-d H:i:s'),
                    'published_at' => date('Y-m-d H:i:s'),
                    'author_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1,
                    'tags' => ['test']
                ]
            ];
        }
        
        // Делаем переменную доступной в представлении
        extract(['articles' => $articles]);
        
        // Подключаем представление
        require __DIR__ . '/../Views/list_articles.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }
        require __DIR__ . '/../Views/create_article.php';
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }

        // TODO: Добавить сохранение статьи
        header('Location: /PHP-APP/public/article');
        exit();
    }

    public function showEditForm($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }
        require __DIR__ . '/../Views/edit_article.php';
    }

    public function update($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }
        // TODO: Добавить обновление статьи
        header('Location: /PHP-APP/public/article');
        exit();
    }

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }
        // TODO: Добавить удаление статьи
        header('Location: /PHP-APP/public/article');
        exit();
    }
}
?>
