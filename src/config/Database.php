<?php
/**
 * Clase Database - Conexión a MySQL
 * PHP 7.0+
 */

class Database {
    
    private $host;
    private $db;
    private $user;
    private $password;
    private $charset;
    private $connection;
    
    public function __construct() {
        // Configuración de la base de datos
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db = getenv('DB_NAME') ?: 'jashei';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->charset = 'utf8mb4';
        
        $this->connect();
    }
    
    public function connect() {
        $this->connection = new mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->db
        );
        
        if ($this->connection->connect_error) {
            die('Error de conexión: ' . $this->connection->connect_error);
        }
        
        $this->connection->set_charset($this->charset);
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function real_escape_string($value) {
        return $this->connection->real_escape_string($value);
    }
    
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function affectedRows() {
        return $this->connection->affected_rows;
    }
    
    public function error() {
        return $this->connection->error;
    }
    
    public function close() {
        $this->connection->close();
    }
    
    public function __destruct() {
        $this->close();
    }
}
