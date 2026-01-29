<?php
// TODO .env
require_once "config.php";


class Database {
    
    private static ?Database $instance = null;
    
    private ?PDO $connection = null;
    
    private string $username;
    private string $password;
    private string $host;
    private string $database;

    
    private function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }


    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

   
    public function connect(): PDO
    {
        if ($this->connection === null) {
            try {
                $this->connection = new PDO(
                    "pgsql:host=$this->host;port=5432;dbname=$this->database",
                    $this->username,
                    $this->password,
                    ["sslmode" => "prefer"]
                );

    
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        
        return $this->connection;
    }

    
    public function disconnect(): void
    {
        $this->connection = null;
    }

    
    public function isConnected(): bool
    {
        return $this->connection !== null;
    }
}