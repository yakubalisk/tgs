<?php 

class Database {
    public $con;
    
    public function __construct() {
        $this->con = new PDO("mysql:host=localhost;dbname=tgs", "root", "");
        $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param string $sql SQL query with placeholders
     * @param array $data [':placeholder' => value]
     */
    public function setData($sql, $data) {
        try {
            $stmt = $this->con->prepare($sql); // Use the class's connection
            $stmt->execute($data);
            return $this->con->lastInsertId(); // Return new ID
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAssoc1($sql, $params = []) {
        try {
            $stmt = $this->con->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DB Fetch Error: " . $e->getMessage());
            return false;
        }
    }    

    public function getAssoc($sql, $params = []) {
        try {
            $stmt = $this->con->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DB Fetch Error: " . $e->getMessage());
            return false;
        }
    }
}
?>