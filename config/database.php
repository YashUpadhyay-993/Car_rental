<?php
// Database configuration
class Database {
    private $host = "sql100.infinityfree.com";
    private $db_name = "if0_41559190_carrental";
    private $username = "if0_41559190";
    private $password = "14VjMeCaO0O"; // 👈 apna real password daalo
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        
        return $this->conn;
    }
}
?>