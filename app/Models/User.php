<?php
namespace app\Models;
use app\Config\Database;

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function create($username, $email, $password, $role = 'author') {
        if ($this->findByEmail($email)) {
            return false;
        }

        $stmt = $this->pdo->prepare("INSERT INTO Users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $success = $stmt->execute([$username, $email, $passwordHash, $role]);
        if (!$success) {
            echo "Create failed. Error: " . implode(", ", $stmt->errorInfo()) . "\n";
        } else {
            echo "User created. Last ID: " . $this->pdo->lastInsertId() . "\n";
            echo "Saved hash: " . $passwordHash . "\n";
        }
        return $success;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            echo "No user found for email: $email\n";
        } else {
            echo "User found: " . print_r($user, true) . "\n";
        }
        return $user;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function verifyPassword($email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("Login attempt: No user found for email: $email");
                return false;
            }

            if (password_verify($password, $user['password_hash'])) {
                error_log("Login successful for user: " . $user['username']);
                return $user;
            }

            error_log("Login attempt: Invalid password for email: $email");
            return false;
        } catch (\PDOException $e) {
            error_log("Database error during login: " . $e->getMessage());
            return false;
        }
    }
}
?>
