<?php
class Koneksi {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'aell';  // Changed from saviorberkah to savior_berkah
    private $conn;

    public function __construct() {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            
            if ($this->conn->connect_error) {
                throw new Exception("MySQL Connection Error: " . $this->conn->connect_error . 
                                  " | Using credentials: " . $this->user . "@" . $this->host . 
                                  " | Database: " . $this->dbname);
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            die("Database connection error. Details: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>