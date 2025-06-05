<?php
namespace app\Models;

use app\Config\Database;
use PDO;

class Article extends ActiveRecordEntity
{
    protected $article_id;
    public $title;
    public $slug;
    public $content;
    public $author_id;
    protected $category_id;
    public $status;
    public $published_at;
    public $created_at;
    protected $username;
    protected $category_name;
    protected $tags = [];

    protected static function getTableName(): string
    {
        return 'Articles';
    }

    protected static function getPrimaryKeyName(): string
    {
        return 'article_id';
    }

    public function delete(): void
    {
        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            // Сначала удаляем связи с тегами
            $deleteTagsStmt = $db->prepare('DELETE FROM Article_Tags WHERE article_id = :article_id');
            $deleteTagsStmt->execute([':article_id' => $this->article_id]);
            error_log("Deleted article tags for article ID: " . $this->article_id);

            // Затем удаляем саму статью
            $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE article_id = :id';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $this->article_id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new \PDOException("Delete query failed");
            }

            $db->commit();
            error_log("Article deleted successfully, ID: " . $this->article_id);
            
            $this->article_id = null;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error deleting article: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAuthorId(): int
    {
        return $this->author_id;
    }

    public function getCategoryId(): int
    {
        error_log("Getting category_id: " . print_r($this->category_id, true));
        return $this->category_id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPublishedAt(): ?string
    {
        return $this->published_at;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setAuthorId(int $authorId): void
    {
        $this->author_id = $authorId;
    }

    public function setCategoryId(int $categoryId): void
    {
        error_log("Setting category_id to: " . $categoryId);
        $this->category_id = $categoryId;
    }

    public function generateSlug(string $string): string
    {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        $db = Database::getInstance();
        $count = $db->prepare('SELECT COUNT(*) as count FROM `' . static::getTableName() . '` WHERE slug = :slug');
        $count->execute([':slug' => $slug]);
        $countResult = $count->fetchColumn();

        if ($countResult > 0) {
            $slug .= '-' . time();
        }

        return $slug;
    }

    public static function findAllWithDetails(): array
    {
        $joins = [
            [
                'type' => 'LEFT',
                'table' => 'Users',
                'alias' => 'u',
                'condition' => 'main.author_id = u.user_id',
                'select' => ['u.username']
            ],
            [
                'type' => 'LEFT',
                'table' => 'Categories',
                'alias' => 'c',
                'condition' => 'main.category_id = c.category_id',
                'select' => ['c.name AS category_name']
            ]
        ];

        $articles = self::findWithDetails($joins, [], 'created_at', 'DESC');

        // Добавляем теги к статьям
        $db = Database::getInstance();
        foreach ($articles as $article) {
            $tagStmt = $db->prepare('
                SELECT t.name FROM Tags t
                JOIN Article_Tags at ON t.tag_id = at.tag_id
                WHERE at.article_id = :article_id
            ');
            $tagStmt->execute([':article_id' => $article->article_id]);
            $article->tags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return $articles;
    }

    public static function findByIdWithDetails(int $id): ?self
    {
        $joins = [
            [
                'type' => 'LEFT',
                'table' => 'Users',
                'alias' => 'u',
                'condition' => 'main.author_id = u.user_id',
                'select' => ['u.username']
            ],
            [
                'type' => 'LEFT',
                'table' => 'Categories',
                'alias' => 'c',
                'condition' => 'main.category_id = c.category_id',
                'select' => ['c.name AS category_name']
            ]
        ];

        $conditions = ['main.article_id = ' . $id];
        error_log("Finding article with ID: " . $id);
        $articles = self::findWithDetails($joins, $conditions);

        if (empty($articles)) {
            error_log("No article found with ID: " . $id);
            return null;
        }

        $article = $articles[0];
        error_log("Found article: " . print_r($article, true));

        // Добавляем теги
        $db = Database::getInstance();
        $tagStmt = $db->prepare('
            SELECT t.name FROM Tags t
            JOIN Article_Tags at ON t.tag_id = at.tag_id
            WHERE at.article_id = :article_id
        ');
        $tagStmt->execute([':article_id' => $article->article_id]);
        $article->tags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);
        error_log("Article tags: " . print_r($article->tags, true));

        return $article;
    }

    public function updateTags(string $tagsString): int
    {
        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            $deleteStmt = $db->prepare('DELETE FROM Article_Tags WHERE article_id = :article_id');
            $deleteStmt->execute([':article_id' => $this->article_id]);

            $tags = array_filter(array_map('trim', explode(',', $tagsString)));
            $count = 0;

            foreach ($tags as $tagName) {
                // Проверяем, есть ли тег
                $tagStmt = $db->prepare('SELECT tag_id FROM Tags WHERE name = :name');
                $tagStmt->execute([':name' => $tagName]);
                $tag = $tagStmt->fetch();

                if (!$tag) {
                    // Вставляем новый тег
                    $slug = $this->generateSlug($tagName);
                    $insertTagStmt = $db->prepare('INSERT INTO Tags (name, slug) VALUES (:name, :slug)');
                    $insertTagStmt->execute([':name' => $tagName, ':slug' => $slug]);
                    $tagId = $db->lastInsertId();
                } else {
                    $tagId = $tag['tag_id'];
                }

                $insertArticleTagStmt = $db->prepare('INSERT INTO Article_Tags (article_id, tag_id) VALUES (:article_id, :tag_id)');
                $insertArticleTagStmt->execute([':article_id' => $this->article_id, ':tag_id' => $tagId]);
                $count++;
            }

            $db->commit();
            return $count;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    protected function getProperties(): array
    {
        $properties = parent::getProperties();
        
        // Удаляем поля, которые не должны сохраняться в базу данных
        unset($properties['username']);
        unset($properties['category_name']);
        unset($properties['tags']);
        
        return $properties;
    }

    // Геттеры для свойств только для чтения
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
