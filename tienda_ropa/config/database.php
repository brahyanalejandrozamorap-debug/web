<?php
class Database {
    private $host = "localhost";
    private $dbname = "tienda_ropa";  // base de datos en phpMyAdmin
    private $username = "root";       // usuario por defecto en XAMPP
    private $password = "";           // en XAMPP 
    private $charset = "utf8mb4";

    public function conectar() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=" . $this->charset;
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdo = new PDO($dsn, $this->username, $this->password, $opciones);
            return $pdo;
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            exit;
        }
    }
}
?>