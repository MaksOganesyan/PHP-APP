<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Articles</title>
    
</head>
<body>
    <h1>Articles</h1>
    <?php if (empty($articles)): ?>
        <p>No articles found.</p>
    <?php else: ?>
        <ul>
        <?php foreach ($articles as $article): ?>
            <li>
                <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
                <p>By <?php echo htmlspecialchars($article['username']); ?> in <?php echo htmlspecialchars($article['category_name']); ?> | Status: <?php echo htmlspecialchars($article['status']); ?> | Created: <?php echo $article['created_at']; ?> | Published: <?php echo $article['published_at'] ?? 'Not published'; ?></p>
                <p>Tags: <?php echo htmlspecialchars(implode(', ', $article['tags'] ?? [])); ?></p>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $article['author_id']): ?>
                    <a href="/PHP-APP/public/article/edit/<?php echo $article['article_id']; ?>">Edit</a> |
                    <a href="/PHP-APP/public/article/delete/<?php echo $article['article_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="/PHP-APP/public/">Back to Home</a>
</body>
</html>
