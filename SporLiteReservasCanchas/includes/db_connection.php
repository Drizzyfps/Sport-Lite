<?php

define('DB_SERVER', '127.0.0.1'); 
define('DB_USERNAME', 'root');  
define('DB_PASSWORD', ''); 
define('DB_NAME', 'sportlite_reservas');

function get_db_connection() {
    $conn = null;
    try {
        $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conn->exec("set names utf8");
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
    }
    return $conn;
}
?>