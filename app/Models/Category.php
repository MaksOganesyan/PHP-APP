<?php
namespace app\Models;
use app\Database\Database;

class Category {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function findAll() {
        $stmt = $this->pdo->query("SELECT * FROM Categories ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Categories WHERE category_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
?>
