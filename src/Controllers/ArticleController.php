<?php
namespace App\Controllers;

class ArticleController {

    public function list() {
        echo "List of Articles";
    }

    public function create() {
        // Показываем форму создания статьи
        require __DIR__ . '/../Views/create_article.php';
    }

    public function store() {
    // Логика сохранения новой статьи (обработка POST)
    // Здесь вставь код сохранения статьи в базу

    header('Location: /PHP-APP/public/articles');
    exit();
}


    public function showEditForm($id) {
        // Показываем форму редактирования статьи с id
        echo "Show edit form for article ID: " . htmlspecialchars($id);
        // Тут можно подключить view с формой и передать $id
    }

    public function update($id) {
        // Логика обновления статьи с id (обработка POST)
        echo "Updating article ID: " . htmlspecialchars($id);
    }

    public function delete($id) {
        // Логика удаления статьи с id
        echo "Deleting article ID: " . htmlspecialchars($id);
    }
}
?>
