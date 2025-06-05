<?php
use app\Models\Category;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($article)) {
    error_log("Article variable is not set in edit_article.php");
    die("Article data not provided.");
}

try {
    $categories = Category::findAll();
    error_log("Article object in view: " . print_r($article, true));
    error_log("Categories in view: " . print_r($categories, true));
    error_log("Session data: " . print_r($_SESSION, true));
    
    if (empty($categories)) {
        Category::createDefaultCategories();
        $categories = Category::findAll();
        error_log("Categories after creation: " . print_r($categories, true));
    }
} catch (\Exception $e) {
    error_log("Error in edit_article.php: " . $e->getMessage());
    echo "Error loading categories: " . $e->getMessage();
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Article</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Добавляем jQuery для отладки -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        console.log('Document ready');
        // Проверяем наличие элементов
        console.log('Form elements:', {
            title: $('#title').val(),
            content: $('#content').val(),
            category: $('#category_id').val(),
            status: $('#status').val()
        });
        
        // Проверяем видимость кнопок
        $('.btn').each(function() {
            console.log('Button:', $(this).text(), 'Visible:', $(this).is(':visible'));
        });
    });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/PHP-APP/public/">Blog</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse show" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/PHP-APP/public/article">Articles</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/PHP-APP/public/logout" class="btn btn-outline-light">Logout</a>
                    <?php else: ?>
                        <a href="/PHP-APP/public/login" class="btn btn-outline-light me-2">Login</a>
                        <a href="/PHP-APP/public/register" class="btn btn-light">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="alert alert-warning">
                You must be logged in to edit an article. <a href="/PHP-APP/public/login">Login here</a>.
            </div>
        <?php else: ?>
            <h1 class="mb-4">Edit Article</h1>
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form method="POST" action="/PHP-APP/public/article/update/<?php echo $article->getId(); ?>" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="title" class="form-label">Title:</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($article->title); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content:</label>
                    <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($article->content); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category:</label>
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-warning">No categories available. Debug info: <?php var_dump($categories); ?></div>
                    <?php else: ?>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                $isSelected = $article->getCategoryId() == $category->getId();
                                error_log("Comparing article category {$article->getCategoryId()} with option {$category->getId()}: " . ($isSelected ? 'true' : 'false'));
                                ?>
                                <option value="<?php echo $category->getId(); ?>" 
                                    <?php echo $isSelected ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category->getName()); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="tags" class="form-label">Tags (comma-separated):</label>
                    <input type="text" class="form-control" id="tags" name="tags" 
                           value="<?php echo htmlspecialchars(implode(', ', $article->getTags())); ?>" 
                           placeholder="e.g., tech, coding">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="draft" <?php echo $article->status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $article->status === 'published' ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Update Article</button>
                    <a href="/PHP-APP/public/article" class="btn btn-secondary">Back to Articles</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
