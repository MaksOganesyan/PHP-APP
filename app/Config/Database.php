<?php
namespace app\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $this->connection = new PDO(
                'mysql:host=localhost;dbname=blog;charset=utf8mb4',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );

            // Инициализируем структуру базы данных
            $this->initializeDatabase();
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }

    private function initializeDatabase(): void
    {
        try {
            $sql = file_get_contents(__DIR__ . '/check_and_create_tables.sql');
            $this->connection->exec($sql);
        } catch (PDOException $e) {
            error_log("Database initialization error: " . $e->getMessage());
            throw new PDOException("Database initialization failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    private function __clone() {}
    
    public function __wakeup() {}
}
