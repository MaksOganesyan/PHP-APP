<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
        session_start();
    }

    public function showRegister() {
        require __DIR__ . '/../Views/register.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->userModel->create($username, $email, $password)) {
                header('Location: /login');
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
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->verifyPassword($email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                header('Location: /');
                exit;
            } else {
                echo "Invalid credentials";
            }
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /');
        exit;
    }
}
?>
