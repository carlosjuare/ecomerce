<?php
class Database {
    private $conn;
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/config.php'; // ruta relativa a db.php
    }

    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->config['host']};dbname={$this->config['db_name']};charset=utf8",
                $this->config['username'],
                $this->config['password']
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            exit("Error de conexión a la base de datos.");
        }
        return $this->conn;
    }
}
