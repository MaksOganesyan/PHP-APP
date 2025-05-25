<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Home</title>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/PHP-APP/public/article">Articles</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="/PHP-APP/public/logout" class="btn btn-outline-light">Logout</a>
                    <?php else: ?>
                        <a href="/PHP-APP/public/login" class="btn btn-outline-light me-2">Login</a>
                        <a href="/PHP-APP/public/register" class="btn btn-light">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row min-vh-100 align-items-center">
            <div class="col-md-8 mx-auto text-center">
                <h1 class="display-4 mb-4">Welcome to Our Blog</h1>
                <p class="lead mb-4">Share your thoughts, ideas, and stories with the world.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <a href="/PHP-APP/public/register" class="btn btn-primary btn-lg px-4 gap-3">Get Started</a>
                        <a href="/PHP-APP/public/login" class="btn btn-outline-secondary btn-lg px-4">Sign In</a>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <a href="/PHP-APP/public/article/create" class="btn btn-success btn-lg px-4">Create New Article</a>
                        <a href="/PHP-APP/public/article" class="btn btn-outline-primary btn-lg px-4">View Articles</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
