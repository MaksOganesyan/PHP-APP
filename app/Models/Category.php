<?php

namespace app\Models;

use app\Config\Database;

class Category extends ActiveRecordEntity
{
    protected $category_id;
    public $name;
    public $slug;
    public $created_at;

    protected static function getTableName(): string
    {
        return 'Categories';
    }

    public function getId(): int
    {
        return $this->category_id;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    public static function findAll(string $orderBy = null, string $direction = 'ASC'): array
    {
        try {
            $sql = 'SELECT * FROM `' . static::getTableName() . '`';
            if ($orderBy !== null) {
                $sql .= ' ORDER BY `' . $orderBy . '` ' . $direction;
            }

            error_log("Category findAll SQL: " . $sql);
            
            $stmt = Database::getInstance()->query($sql);
            $results = $stmt->fetchAll();
            
            error_log("Category findAll raw results: " . print_r($results, true));
            
            $categories = static::createEntitiesFromRows($results);
            
            error_log("Category objects created: " . count($categories));
            foreach ($categories as $category) {
                error_log("Category: " . print_r($category, true));
            }
            
            return $categories;
        } catch (\Exception $e) {
            error_log("Error in Category::findAll: " . $e->getMessage());
            throw $e;
        }
    }

    public static function createDefaultCategories(): void
    {
        try {
            $defaultCategories = [
                ['name' => 'General', 'slug' => 'general'],
                ['name' => 'Technology', 'slug' => 'technology'],
                ['name' => 'Programming', 'slug' => 'programming'],
                ['name' => 'Lifestyle', 'slug' => 'lifestyle'],
                ['name' => 'Travel', 'slug' => 'travel']
            ];

            foreach ($defaultCategories as $categoryData) {
                $category = new self();
                $category->name = $categoryData['name'];
                $category->slug = $categoryData['slug'];
                $category->created_at = date('Y-m-d H:i:s');
                try {
                    $category->save();
                    error_log("Created category: " . $categoryData['name']);
                } catch (\Exception $e) {
                    error_log("Error creating category {$categoryData['name']}: " . $e->getMessage());
                    continue;
                }
            }
        } catch (\Exception $e) {
            error_log("Error in createDefaultCategories: " . $e->getMessage());
            throw $e;
        }
    }
}
