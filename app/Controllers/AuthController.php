<?php
namespace app\Controllers;

use app\Models\User;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function showRegister() {
        require __DIR__ . '/../Views/register.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($email) || empty($password)) {
                echo "All fields are required";
                exit;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "Invalid email format";
                exit;
            }
            if ($this->userModel->findByEmail($email)) {
                echo "Email already exists";
                exit;
            }

            if ($this->userModel->create($username, $email, $password)) {
                header('Location: /PHP-APP/public/login');
                exit;
            } else {
                echo "Registration failed";
            }
        }
    }

    public function showLogin() {
        require __DIR__ . '/../Views/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['errors'] = ['Email and password are required'];
                header('Location: /PHP-APP/public/login');
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['errors'] = ['Invalid email format'];
                header('Location: /PHP-APP/public/login');
                exit;
            }

            $user = $this->userModel->verifyPassword($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: /PHP-APP/public/article');
                exit;
            } else {
                $_SESSION['errors'] = ['Invalid credentials'];
                header('Location: /PHP-APP/public/login');
                exit;
            }
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /PHP-APP/public/');
        exit;
    }
}
?>
