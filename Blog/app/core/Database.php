<?php

require_once '../app/config/config.php';


class Database {
    private static $instance;
    private $db;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
        $this->db = new PDO($dsn, DB_USER, DB_PASS);
        $this->db->exec("SET NAMES 'utf8'");
    }

    // Get the single instance of the database connection
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance->db;
    }

    // Query method to execute SQL queries
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

?>