<?php

namespace app\Controllers;

use app\Models\User;

class AuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function showLogin()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/article');
            exit;
        }
        require __DIR__ . '/../Views/login.php';
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /PHP-APP/public/login');
            exit;
        }

        $errors = [];
        if (empty($_POST['email'])) {
            $errors[] = 'Email is required';
        }
        if (empty($_POST['password'])) {
            $errors[] = 'Password is required';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /PHP-APP/public/login');
            exit;
        }

        try {
            $user = User::findByEmail($_POST['email']);
            if (!$user || !$user->verifyPassword($_POST['password'])) {
                $_SESSION['errors'] = ['Invalid email or password'];
                header('Location: /PHP-APP/public/login');
                exit;
            }

            // Очищаем старые данные сессии
            session_unset();
            
            // Устанавливаем новые данные
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();
            $_SESSION['role'] = $user->getRole();
            
            header('Location: /PHP-APP/public/article');
            exit;
        } catch (\Exception $e) {
            error_log("Error in AuthController::login: " . $e->getMessage());
            $_SESSION['errors'] = ['An error occurred during login'];
            header('Location: /PHP-APP/public/login');
            exit;
        }
    }

    public function showRegister()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /PHP-APP/public/article');
            exit;
        }
        require __DIR__ . '/../Views/register.php';
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /PHP-APP/public/register');
            exit;
        }

        $errors = [];
        if (empty($_POST['username'])) {
            $errors[] = 'Username is required';
        }
        if (empty($_POST['email'])) {
            $errors[] = 'Email is required';
        }
        if (empty($_POST['password'])) {
            $errors[] = 'Password is required';
        }
        if ($_POST['password'] !== ($_POST['confirm_password'] ?? '')) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /PHP-APP/public/register');
            exit;
        }

        try {
            $existingUser = User::findByEmail($_POST['email']);
            if ($existingUser) {
                $_SESSION['errors'] = ['Email already registered'];
                header('Location: /PHP-APP/public/register');
                exit;
            }

            User::createUser($_POST['username'], $_POST['email'], $_POST['password']);
            $_SESSION['success'] = 'Registration successful! Please login.';
            header('Location: /PHP-APP/public/login');
            exit;
        } catch (\Exception $e) {
            error_log("Error in AuthController::register: " . $e->getMessage());
            $_SESSION['errors'] = ['An error occurred during registration'];
            header('Location: /PHP-APP/public/register');
            exit;
        }
    }

    public function logout()
    {
        // Очищаем все данные сессии
        session_unset();
        
        // Уничтожаем сессию
        session_destroy();
        
        // Очищаем куки сессии
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Очищаем массив сессии
        $_SESSION = array();
        
        header('Location: /PHP-APP/public/login');
        exit;
    }
}
