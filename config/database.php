<?php
class Database
{
    private static $instance = null;
    private $conn;

    private $host = 'localhost';
    private $db_name = 'TDW';
    private $username = 'admin';
    private $password = 'admin';

    private function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    // Empêcher le clonage
    private function __clone()
    {
    }

    // Empêcher la désérialisation
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>