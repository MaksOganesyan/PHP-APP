<?php
namespace app\Models;
use app\Config\Database;

class Article {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO Articles (title, content, author_id, category_id, status, published_at) 
                    VALUES (:title, :content, :author_id, :category_id, :status, :published_at)";
            
            $published_at = $data['status'] === 'published' ? date('Y-m-d H:i:s') : null;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':content' => $data['content'],
                ':author_id' => $_SESSION['user_id'],
                ':category_id' => $data['category_id'],
                ':status' => $data['status'],
                ':published_at' => $published_at
            ]);

            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error creating article: " . $e->getMessage());
            return false;
        }
    }

    public function findAll() {
        try {
            $sql = "SELECT a.*, u.username, c.name as category_name 
                   FROM Articles a 
                   JOIN Users u ON a.author_id = u.user_id 
                   JOIN Categories c ON a.category_id = c.category_id 
                   ORDER BY a.created_at DESC";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching articles: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        try {
            $sql = "SELECT a.*, u.username, c.name as category_name 
                   FROM Articles a 
                   JOIN Users u ON a.author_id = u.user_id 
                   JOIN Categories c ON a.category_id = c.category_id 
                   WHERE a.article_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching article by ID: " . $e->getMessage());
            return null;
        }
    }
} 
