<?php
use app\Models\Category;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($article)) {
    die("Article data not provided.");
}

try {
    $categoryModel = new Category();
    $categories = $categoryModel->findAll();
} catch (\Exception $e) {
    error_log("Error in edit_article.php: " . $e->getMessage());
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Article</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/PHP-APP/public/">Blog</a>
            <div class="collapse navbar-collapse">
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

            <form method="POST" action="/PHP-APP/public/article/update/<?php echo $article['article_id']; ?>" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="title" class="form-label">Title:</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content:</label>
                    <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category:</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php if (empty($categories)): ?>
                            <option value="" disabled>No categories available</option>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo $article['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tags" class="form-label">Tags (comma-separated):</label>
                    <input type="text" class="form-control" id="tags" name="tags" 
                           value="<?php echo htmlspecialchars(implode(', ', $article['tags'] ?? [])); ?>" 
                           placeholder="e.g., tech, coding">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="draft" <?php echo $article['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $article['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
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
