<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Models\Category;
$categoryModel = new Category();
$categories = $categoryModel->findAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Article</title>
</head>
<body>
    <h1>Edit Article</h1>
    <form method="POST" action="/PHP-APP/public/article/update/<?php echo $article['article_id']; ?>">
        <div>
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
        </div>
        <div>
            <label for="content">Content:</label>
            <textarea id="content" name="content" required><?php echo htmlspecialchars($article['content']); ?></textarea>
        </div>
        <div>
            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>" <?php echo $article['category_id'] == $category['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="tags">Tags (comma-separated):</label>
            <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars(implode(', ', $article['tags'] ?? [])); ?>">
        </div>
        <div>
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="draft" <?php echo $article['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo $article['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
            </select>
        </div>
        <button type="submit">Update</button>
    </form>
    <a href="/PHP-APP/public/">Back to Home</a>
</body>
</html>
