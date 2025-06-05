<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/PHP-APP/public/">Blog</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/PHP-APP/public/article">Articles</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="navbar-text me-3">
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                        </span>
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
        <h1 class="mb-4">Articles</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="mb-3">
                <a href="/PHP-APP/public/article/create" class="btn btn-primary">Create New Article</a>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

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

        <?php if (empty($articles)): ?>
            <div class="alert alert-info">
                <?php if (isset($_SESSION['user_id'])): ?>
                    No articles yet. <a href="/PHP-APP/public/article/create">Create your first article?</a>
                <?php else: ?>
                    No articles yet.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($articles as $article): ?>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($article->title); ?></h5>
                                <p class="card-text">
                                    By <?php echo htmlspecialchars($article->getUsername()); ?> in <?php echo htmlspecialchars($article->getCategoryName()); ?> | 
                                    Status: <?php echo htmlspecialchars($article->status); ?> | 
                                    Created: <?php echo htmlspecialchars($article->created_at ?? 'N/A'); ?> | 
                                    Published: <?php echo htmlspecialchars($article->published_at ?? 'N/A'); ?>
                                </p>
                                <p class="card-text">
                                    <?php echo nl2br(htmlspecialchars(substr($article->content, 0, 200) . (strlen($article->content) > 200 ? '...' : ''))); ?>
                                </p>
                                <p class="card-text">
                                    Tags: <?php echo htmlspecialchars(implode(', ', $article->getTags())); ?>
                                </p>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article->getAuthorId()): ?>
                                    <div class="d-flex">
                                        <a href="/PHP-APP/public/article/edit/<?php echo $article->getId(); ?>" class="btn btn-primary btn-sm me-2">Edit</a>
                                        <form action="/PHP-APP/public/article/delete/<?php echo $article->getId(); ?>" method="POST" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this article?')">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
