<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/PHP-APP/public/">Blog</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/PHP-APP/public/article">Articles</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/PHP-APP/public/logout" class="btn btn-outline-light me-2">Logout</a>
                    <?php else: ?>
                        <a href="/PHP-APP/public/login" class="btn btn-outline-light me-2">Login</a>
                        <a href="/PHP-APP/public/register" class="btn btn-light">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Articles</h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/PHP-APP/public/article/create" class="btn btn-success">Create New Article</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <?php 
                foreach ($_SESSION['errors'] as $error) {
                    echo htmlspecialchars($error) . '<br>';
                }
                unset($_SESSION['errors']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($articles)): ?>
            <div class="alert alert-info">
                <?php if (isset($_SESSION['user_id'])): ?>
                    Записи ещё не созданы. <a href="/PHP-APP/public/article/create" class="alert-link">Создать первую запись</a>?
                <?php else: ?>
                    Записи ещё не созданы.
                <?php endif; ?>
            </div>
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
                                <form action="/PHP-APP/public/article/delete/<?php echo $article['article_id']; ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
