<?php
namespace app\Models;

use app\Config\Database;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionProperty;

abstract class ActiveRecordEntity
{
    abstract protected static function getTableName(): string;

    protected static function getPrimaryKeyName(): string
    {
        return static::getTableName() === 'Users' ? 'user_id' : 
               (static::getTableName() === 'Articles' ? 'article_id' : 
               (static::getTableName() === 'Categories' ? 'category_id' : 
               (static::getTableName() === 'Tags' ? 'tag_id' : 'id')));
    }

    public function getId(): int
    {
        $primaryKey = static::getPrimaryKeyName();
        return $this->$primaryKey;
    }

    protected function getProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();
            $properties[$propertyName] = $this->$propertyName;
        }

        return $properties;
    }

    /**
     * Сохраняет текущий объект в базе — создаёт или обновляет запись
     */
    public function save(): void
    {
        $primaryKey = static::getPrimaryKeyName();
        if (isset($this->$primaryKey)) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    /**
     * Вставка новой записи
     */
    protected function insert(): void
    {
        $props = $this->getProperties();
        $primaryKey = static::getPrimaryKeyName();

        if (isset($props[$primaryKey])) {
            unset($props[$primaryKey]);
        }

        $columns = array_keys($props);
        $params = array_map(fn($col) => ":$col", $columns);

        $sql = 'INSERT INTO `' . static::getTableName() . '` (' . implode(', ', $columns) . ')
                VALUES (' . implode(', ', $params) . ')';

        $stmt = Database::getInstance()->prepare($sql);
        foreach ($props as $column => $value) {
            $stmt->bindValue(":$column", $value);
        }

        if (!$stmt->execute()) {
            throw new PDOException("Insert query failed");
        }

        $this->$primaryKey = (int)Database::getInstance()->lastInsertId();
    }

    /**
     * Обновление существующей записи
     */
    protected function update(): void
    {
        $props = $this->getProperties();
        $primaryKey = static::getPrimaryKeyName();

        if (!isset($this->$primaryKey)) {
            throw new \LogicException("Can't update entity without primary key");
        }

        if (isset($props[$primaryKey])) {
            unset($props[$primaryKey]);
        }

        $columns = [];
        foreach ($props as $column => $value) {
            if ($value !== null) {
                $columns[] = "`$column` = :$column";
            }
        }

        $sql = 'UPDATE `' . static::getTableName() . '` SET ' . implode(', ', $columns) . ' WHERE ' . $primaryKey . ' = :id';

        $stmt = Database::getInstance()->prepare($sql);
        foreach ($props as $column => $value) {
            if ($value !== null) {
                $stmt->bindValue(":$column", $value);
            }
        }
        $stmt->bindValue(':id', $this->$primaryKey, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new PDOException("Update query failed");
        }
    }

    /**
     * Удаление записи из базы
     */
    public function delete(): void
    {
        $primaryKey = static::getPrimaryKeyName();
        if (!isset($this->$primaryKey)) {
            throw new \LogicException("Can't delete entity without primary key");
        }

        $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE ' . $primaryKey . ' = :id';
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->bindValue(':id', $this->$primaryKey, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new PDOException("Delete query failed");
        }

        $this->$primaryKey = 0;
    }

    /**
     * Поиск записи по ID
     */
    public static function findById(int $id): ?self
    {
        $primaryKey = static::getPrimaryKeyName();
        return static::findOneBy($primaryKey, $id);
    }

    public static function findOneBy(string $column, $value): ?self
    {
        $results = static::findBy($column, $value);
        return !empty($results) ? $results[0] : null;
    }

    public static function findBy(string $column, $value, string $orderBy = null, string $direction = 'ASC'): array
    {
        if (empty($column)) {
            throw new \InvalidArgumentException('Column name cannot be empty');
        }

        $sql = 'SELECT * FROM `' . static::getTableName() . '` WHERE `' . $column . '` = :value';
        
        if ($orderBy !== null) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $direction;
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute([':value' => $value]);

        return static::createEntitiesFromRows($stmt->fetchAll());
    }

    /**
     * Возвращает все записи
     */
    public static function findAll(string $orderBy = null, string $direction = 'ASC'): array
    {
        $sql = 'SELECT * FROM `' . static::getTableName() . '`';
        
        if ($orderBy !== null) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $direction;
        }

        $stmt = Database::getInstance()->query($sql);
        return static::createEntitiesFromRows($stmt->fetchAll());
    }

    public static function findWithDetails(array $joins = [], array $conditions = [], string $orderBy = null, string $direction = 'ASC'): array
    {
        $alias = 'main';
        $sql = 'SELECT ' . $alias . '.* ';
        
        foreach ($joins as $join) {
            if (!empty($join['select'])) {
                $sql .= ', ' . implode(', ', $join['select']);
            }
        }
        
        $sql .= ' FROM `' . static::getTableName() . '` ' . $alias;
        
        foreach ($joins as $join) {
            $sql .= ' ' . $join['type'] . ' JOIN ' . $join['table'] . ' ' . $join['alias'] . 
                   ' ON ' . $join['condition'];
        }
        
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        
        if ($orderBy !== null) {
            $sql .= ' ORDER BY `' . $orderBy . '` ' . $direction;
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute();

        return static::createEntitiesFromRows($stmt->fetchAll());
    }

    protected static function createEntitiesFromRows(array $rows): array
    {
        $entities = [];
        foreach ($rows as $row) {
            $entities[] = static::createEntityFromRow($row);
        }
        return $entities;
    }

    protected static function createEntityFromRow(array $row): self
    {
        $entity = new static();
        $reflection = new ReflectionClass(static::class);
        
        foreach ($row as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($entity, $value);
            }
        }
        
        return $entity;
    }
}
