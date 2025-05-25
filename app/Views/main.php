<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Welcome to Our Site</h1>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">User Account</h5>
                        <p class="card-text">Access your account or create a new one.</p>
                        <div class="d-flex gap-2">
                            <a href="/PHP-APP/public/login" class="btn btn-primary">Login</a>
                            <a href="/PHP-APP/public/register" class="btn btn-secondary">Register</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Articles</h5>
                        <p class="card-text">Manage your articles.</p>
                        <a href="/PHP-APP/public/article" class="btn btn-success">View Articles</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
