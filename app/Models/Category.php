<?php
namespace app\Models;
use app\Config\Database;

class Category {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (\Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new \Exception("Could not connect to database");
        }
    }

    public function findAll() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM Categories ORDER BY name ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Categories WHERE category_id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching category by ID: " . $e->getMessage());
            return null;
        }
    }
}
?>
