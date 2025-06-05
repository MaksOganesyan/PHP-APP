<?php
namespace app\Controllers;

use app\Models\Article;
use app\Models\Category;

class ArticleController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function checkAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/login');
            exit;
        }
    }

    private function checkArticleOwnership(Article $article): void
    {
        if ($article->author_id !== $_SESSION['user_id']) {
            $_SESSION['errors'] = ['You do not have permission to edit this article'];
            header('Location: /PHP-APP/public/article');
            exit;
        }
    }

    public function list()
    {
        try {
            $articles = Article::findAllWithDetails();
            $categories = Category::findAll();
            require __DIR__ . '/../Views/list_articles.php';
        } catch (\Exception $e) {
            error_log("Error in ArticleController::list: " . $e->getMessage());
            $articles = [];
            $categories = [];
            require __DIR__ . '/../Views/list_articles.php';
        }
    }

    public function create()
    {
        $this->checkAuth();

        $categories = Category::findAll();
        require __DIR__ . '/../Views/create_article.php';
    }

    public function store()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /PHP-APP/public/article/create');
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
            header('Location: /PHP-APP/public/article/create');
            exit;
        }

        try {
            $article = new Article();
            $article->title = $_POST['title'];
            $article->slug = $article->generateSlug($_POST['title']);
            $article->content = $_POST['content'];
            $article->setAuthorId($_SESSION['user_id']);
            $article->setCategoryId((int)$_POST['category_id']);
            $article->status = $_POST['status'];
            $article->published_at = ($_POST['status'] === 'published') ? date('Y-m-d H:i:s') : null;
            $article->created_at = date('Y-m-d H:i:s');

            $article->save();

            if (!empty($_POST['tags'])) {
                $article->updateTags($_POST['tags']);
            }

            header('Location: /PHP-APP/public/article');
            exit;
        } catch (\Exception $e) {
            error_log("Error in ArticleController::store: " . $e->getMessage());
            $_SESSION['errors'] = ["Error saving article: " . $e->getMessage()];
            header('Location: /PHP-APP/public/article/create');
            exit;
        }
    }

    public function showEditForm(int $id)
    {
        $this->checkAuth();

        try {
            $article = Article::findByIdWithDetails($id);
            if (!$article) {
                $_SESSION['errors'] = ['Article not found'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            $this->checkArticleOwnership($article);

            $categories = Category::findAll();
            
            error_log("Article data in showEditForm: " . print_r($article, true));
            error_log("Categories in showEditForm: " . print_r($categories, true));
            
            if (empty($categories)) {
                error_log("Categories array is empty in showEditForm");
            }

            require __DIR__ . '/../Views/edit_article.php';
        } catch (\Exception $e) {
            error_log("Error in ArticleController::showEditForm: " . $e->getMessage());
            $_SESSION['errors'] = ["Error loading article: " . $e->getMessage()];
            header('Location: /PHP-APP/public/article');
            exit;
        }
    }

    public function update(int $id)
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /PHP-APP/public/article');
            exit;
        }

        try {
            $article = Article::findById($id);
            if (!$article) {
                $_SESSION['errors'] = ['Article not found'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            $this->checkArticleOwnership($article);

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
                header('Location: /PHP-APP/public/article/edit/' . $id);
                exit;
            }

            $article->title = $_POST['title'];
            if ($article->title !== $_POST['title']) {
                $article->slug = $article->generateSlug($_POST['title']);
            }
            $article->content = $_POST['content'];
            $article->setCategoryId((int)$_POST['category_id']);
            $article->status = $_POST['status'];
            
            if ($article->status === 'published' && !$article->published_at) {
                $article->published_at = date('Y-m-d H:i:s');
            }

            $article->save();

            if (isset($_POST['tags'])) {
                $article->updateTags($_POST['tags']);
            }

            $_SESSION['success'] = 'Article updated successfully';
            header('Location: /PHP-APP/public/article');
            exit;
        } catch (\Exception $e) {
            error_log("Error in ArticleController::update: " . $e->getMessage());
            $_SESSION['errors'] = ["Error updating article: " . $e->getMessage()];
            header('Location: /PHP-APP/public/article/edit/' . $id);
            exit;
        }
    }

    public function delete(int $id)
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /PHP-APP/public/article');
            exit;
        }

        try {
            $article = Article::findById($id);
            if (!$article) {
                $_SESSION['errors'] = ['Article not found'];
                header('Location: /PHP-APP/public/article');
                exit;
            }

            $this->checkArticleOwnership($article);

            $article->delete();

            $_SESSION['success'] = 'Article deleted successfully';
            header('Location: /PHP-APP/public/article');
            exit;
        } catch (\Exception $e) {
            error_log("Error in ArticleController::delete: " . $e->getMessage());
            $_SESSION['errors'] = ["Error deleting article: " . $e->getMessage()];
            header('Location: /PHP-APP/public/article');
            exit;
        }
    }
}
