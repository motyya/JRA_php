<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = 'localhost';
        $dbname = 'jra_website';
        $username = 'root';
        $password = 'g2hyz5k';
        
        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}

// Функция для удобства
function getDB() {
    return Database::getInstance();
}
?>