<?php
namespace App\Controllers;

class ArticleController {
    public function index() {
        echo "List of Articles";
    }

    public function create() {
        require __DIR__ . '/../Views/create_article.php';
    }
}
?>
