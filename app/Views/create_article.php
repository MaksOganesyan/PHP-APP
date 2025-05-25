<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Models\Category;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$categoryModel = new Category();
$categories = $categoryModel->findAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Article</title>
</head>
<body>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <p>You must be logged in to create an article. <a href="/PHP-APP/public/login">Login here</a>.</p>
    <?php else: ?>
        <h1>Create a New Article</h1>
        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <form method="POST" action="/PHP-APP/public/article/create">
            <div>
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div>
                <label for="content">Content:</label>
                <textarea id="content" name="content" required></textarea>
            </div>
            <div>
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="tags">Tags (comma-separated):</label>
                <input type="text" id="tags" name="tags" placeholder="e.g., tech, coding">
            </div>
            <div>
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
            <button type="submit">Create</button>
        </form>
    <?php endif; ?>
    <a href="/PHP-APP/public/">Back to Home</a>
</body>
</html>
