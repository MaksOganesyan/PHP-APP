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
            // Получаем статьи с основной информацией
            $sql = "SELECT a.*, u.username, c.name as category_name 
                   FROM Articles a 
                   JOIN Users u ON a.author_id = u.user_id 
                   JOIN Categories c ON a.category_id = c.category_id 
                   ORDER BY a.created_at DESC";
            
            $stmt = $this->pdo->query($sql);
            $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Получаем теги для каждой статьи
            foreach ($articles as &$article) {
                $sql = "SELECT t.name 
                       FROM Tags t 
                       JOIN Article_Tags at ON t.tag_id = at.tag_id 
                       WHERE at.article_id = ?";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$article['article_id']]);
                $tags = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                $article['tags'] = $tags;
            }

            return $articles;
        } catch (\PDOException $e) {
            error_log("Error fetching articles: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        try {
            // Получаем основную информацию о статье
            $sql = "SELECT a.*, u.username, c.name as category_name 
                   FROM Articles a 
                   JOIN Users u ON a.author_id = u.user_id 
                   JOIN Categories c ON a.category_id = c.category_id 
                   WHERE a.article_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $article = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($article) {
                // Получаем теги для статьи
                $sql = "SELECT t.name 
                       FROM Tags t 
                       JOIN Article_Tags at ON t.tag_id = at.tag_id 
                       WHERE at.article_id = ?";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$id]);
                $tags = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                $article['tags'] = $tags;
            }

            return $article;
        } catch (\PDOException $e) {
            error_log("Error fetching article by ID: " . $e->getMessage());
            return null;
        }
    }

    public function update($data) {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE Articles 
                    SET title = :title, 
                        content = :content, 
                        category_id = :category_id, 
                        status = :status,
                        published_at = :published_at
                    WHERE article_id = :article_id 
                    AND author_id = :author_id";
            
            $published_at = $data['status'] === 'published' ? date('Y-m-d H:i:s') : null;
            
            $params = [
                ':article_id' => $data['article_id'],
                ':title' => $data['title'],
                ':content' => $data['content'],
                ':category_id' => $data['category_id'],
                ':status' => $data['status'],
                ':published_at' => $published_at,
                ':author_id' => $_SESSION['user_id']
            ];

            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            error_log("Rows affected: " . $stmt->rowCount());

            // Обновляем теги, если они есть
            if (isset($data['tags'])) {
                // Удаляем старые связи
                $this->deleteArticleTags($data['article_id']);
                
                // Добавляем новые теги
                if (!empty($data['tags'])) {
                    $this->saveTags($data['article_id'], $data['tags']);
                }
            }

            $this->pdo->commit();
            return $result && $stmt->rowCount() > 0;

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error updating article: " . $e->getMessage());
            return false;
        }
    }

    private function deleteArticleTags($articleId) {
        $sql = "DELETE FROM Article_Tags WHERE article_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$articleId]);
    }

    private function saveTags($articleId, $tagString) {
        // Разбиваем строку тегов и очищаем их
        $tagNames = array_map('trim', explode(',', $tagString));
        $tagNames = array_filter($tagNames); // Удаляем пустые значения

        foreach ($tagNames as $tagName) {
            // Создаем или получаем существующий тег
            $tagId = $this->getOrCreateTag($tagName);
            
            // Добавляем связь статьи с тегом
            $sql = "INSERT IGNORE INTO Article_Tags (article_id, tag_id) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$articleId, $tagId]);
        }
    }

    private function getOrCreateTag($tagName) {
        // Пытаемся найти существующий тег
        $sql = "SELECT tag_id FROM Tags WHERE name = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tagName]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return $result['tag_id'];
        }

        // Если тег не найден, создаем новый
        $sql = "INSERT INTO Tags (name, slug) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tagName, $this->createSlug($tagName)]);
        
        return $this->pdo->lastInsertId();
    }

    private function createSlug($text) {
        // Транслитерация и создание slug
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    public function delete($articleId, $userId) {
        try {
            $this->pdo->beginTransaction();

            // Проверяем, существует ли статья и принадлежит ли она пользователю
            $sql = "SELECT article_id FROM Articles WHERE article_id = ? AND author_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$articleId, $userId]);
            
            if (!$stmt->fetch()) {
                $this->pdo->rollBack();
                return false;
            }

            // Удаляем связи с тегами
            $sql = "DELETE FROM Article_Tags WHERE article_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$articleId]);

            // Удаляем саму статью
            $sql = "DELETE FROM Articles WHERE article_id = ? AND author_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$articleId, $userId]);

            $this->pdo->commit();
            return $stmt->rowCount() > 0;

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting article: " . $e->getMessage());
            return false;
        }
    }
} 
