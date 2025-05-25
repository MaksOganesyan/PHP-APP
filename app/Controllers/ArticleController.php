<?php
namespace app\Controllers;

class ArticleController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function list() {
        try {
            $articleModel = new \app\Models\Article();
            $articles = $articleModel->findAll();
            
            //! Подключение представления
            require __DIR__ . '/../Views/list_articles.php';
        } catch (\Exception $e) {
            error_log("Error in ArticleController::list: " . $e->getMessage());
            $articles = [];
            require __DIR__ . '/../Views/list_articles.php';
        }
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

        // Проверка на тип (POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /PHP-APP/public/article/create');
            exit;
        }

        //* Валидация данных
        $errors = [];
        
        if (empty($_POST['title'])) {
            $errors[] = "Title is required";
        }
        if (empty($_POST['content'])) {
            $errors[] = "Content is required";
        }
        if (empty($_POST['category_id'])) {
            $errors[] = "Category is required";
        }
        if (empty($_POST['status'])) {
            $errors[] = "Status is required";
        }

        //! Проверка на ошибки в таком случае вернусь на ошибку
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /PHP-APP/public/article/create');
            exit;
        }

        try {
            $articleModel = new \app\Models\Article();
            
            $articleData = [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'category_id' => $_POST['category_id'],
                'status' => $_POST['status']
            ];

            $result = $articleModel->create($articleData);

            if ($result) {
                header('Location: /PHP-APP/public/article');
                exit();
            } else {
                $_SESSION['errors'] = ['Failed to create article. Please try again.'];
                header('Location: /PHP-APP/public/article/create');
                exit();
            }
        } catch (\Exception $e) {
            error_log("Error in ArticleController::store: " . $e->getMessage());
            $_SESSION['errors'] = ['An error occurred while creating the article.'];
            header('Location: /PHP-APP/public/article/create');
            exit();
        }
    }

    public function showEditForm($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }

        try {
            $articleModel = new \app\Models\Article();
            $article = $articleModel->findById($id);

            if (!$article) {
                $_SESSION['errors'] = ['Article not found'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            
            if ($article['author_id'] !== $_SESSION['user_id']) {
                $_SESSION['errors'] = ['You are not authorized to edit this article'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            require __DIR__ . '/../Views/edit_article.php';
        } catch (\Exception $e) {
            error_log("Error in ArticleController::showEditForm: " . $e->getMessage());
            $_SESSION['errors'] = ['An error occurred while loading the article'];
            header('Location: /PHP-APP/public/article');
            exit;
        }
    }

    public function update($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }

       
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /PHP-APP/public/article');
            exit;
        }

        try {
            $articleModel = new \app\Models\Article();
            
           
            $article = $articleModel->findById($id);
            error_log("Article data from DB: " . print_r($article, true));
            
            if (!$article) {
                $_SESSION['errors'] = ['Article not found'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            if ($article['author_id'] !== $_SESSION['user_id']) {
                $_SESSION['errors'] = ['You are not authorized to edit this article'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            
            $errors = [];
            if (empty($_POST['title'])) {
                $errors[] = "Title is required";
            }
            if (empty($_POST['content'])) {
                $errors[] = "Content is required";
            }
            if (empty($_POST['category_id'])) {
                $errors[] = "Category is required";
            }
            if (empty($_POST['status'])) {
                $errors[] = "Status is required";
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                header("Location: /PHP-APP/public/article/edit/{$id}");
                exit;
            }

            
            $articleData = [
                'article_id' => $id,
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'category_id' => $_POST['category_id'],
                'status' => $_POST['status'],
                'tags' => $_POST['tags']
            ];

            error_log("Update data: " . print_r($articleData, true));
            error_log("Session user_id: " . $_SESSION['user_id']);

            // Обновляю статью
            $result = $articleModel->update($articleData);
            error_log("Update result: " . ($result ? 'true' : 'false'));

            if ($result) {
                header('Location: /PHP-APP/public/article');
                exit;
            } else {
                $_SESSION['errors'] = ['Failed to update article'];
                header("Location: /PHP-APP/public/article/edit/{$id}");
                exit;
            }
        } catch (\Exception $e) {
            error_log("Error in ArticleController::update: " . $e->getMessage());
            $_SESSION['errors'] = ['An error occurred while updating the article'];
            header("Location: /PHP-APP/public/article/edit/{$id}");
            exit;
        }
    }

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }

        try {
            $articleModel = new \app\Models\Article();
            
            // Проверка существования статьи и права доступа
            $article = $articleModel->findById($id);
            
            if (!$article) {
                $_SESSION['errors'] = ['Article not found'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            if ($article['author_id'] !== $_SESSION['user_id']) {
                $_SESSION['errors'] = ['You are not authorized to delete this article'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            
            if ($articleModel->delete($id, $_SESSION['user_id'])) {
                $_SESSION['success'] = 'Article successfully deleted';
            } else {
                $_SESSION['errors'] = ['Failed to delete article'];
            }

        } catch (\Exception $e) {
            error_log("Error in ArticleController::delete: " . $e->getMessage());
            $_SESSION['errors'] = ['An error occurred while deleting the article'];
        }

        header('Location: /PHP-APP/public/article');
        exit;
    }
}
?>
