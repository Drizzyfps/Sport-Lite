<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sportlite_login";

$conn = null; // Inicializamos la conexión como null

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Establecer el modo de error PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Opcional: Establecer el modo de recuperación de filas por defecto a asociativo
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error de conexión a la base de datos: " . $e->getMessage();
}
?>