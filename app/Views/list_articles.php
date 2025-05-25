<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Articles</h1>
        <?php if (empty($articles)): ?>
            <p>No articles found.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($articles as $article): ?>
                    <div class="list-group-item">
                        <h2 class="h4"><?php echo htmlspecialchars($article['title']); ?></h2>
                        <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
                        <p class="text-muted">
                            By <?php echo htmlspecialchars($article['username']); ?> 
                            in <?php echo htmlspecialchars($article['category_name']); ?> | 
                            Status: <?php echo htmlspecialchars($article['status']); ?> | 
                            Created: <?php echo $article['created_at']; ?> | 
                            Published: <?php echo $article['published_at'] ?? 'Not published'; ?>
                        </p>
                        <p class="mb-2">Tags: <?php echo htmlspecialchars(implode(', ', $article['tags'] ?? [])); ?></p>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $article['author_id']): ?>
                            <div class="btn-group">
                                <a href="/PHP-APP/public/article/edit/<?php echo $article['article_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="/PHP-APP/public/article/delete/<?php echo $article['article_id']; ?>" 
                                   onclick="return confirm('Are you sure?')" 
                                   class="btn btn-sm btn-outline-danger">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="mt-3">
            <a href="/PHP-APP/public/" class="btn btn-primary">Back to Home</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
