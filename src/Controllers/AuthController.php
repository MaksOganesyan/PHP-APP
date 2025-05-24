<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Показать страницу регистрации
    public function showRegister() {
        require __DIR__ . '/../Views/register.php';
    }

    // Обработка регистрации
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Валидация
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

            // Хэширование происходит внутри create()
            if ($this->userModel->create($username, $email, $password)) {
                header('Location: /PHP-APP/public/login');
                exit;
            } else {
                echo "Registration failed";
            }
        }
    }

    // Показать страницу входа
    public function showLogin() {
        require __DIR__ . '/../Views/login.php';
    }

    // Обработка входа
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->verifyPassword($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                header('Location: /PHP-APP/public/');
                exit;
            } else {
                echo "Invalid credentials";
            }
        }
    }

    // Выход из системы
    public function logout() {
        session_destroy();
        header('Location: /PHP-APP/public/');
        exit;
    }
}
?>
