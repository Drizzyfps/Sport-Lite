<?php
$host = "localhost";
$user = "root";
$pass = ""; // si usas XAMPP, normalmente no hay contraseña
$db = "sport_lite";

$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

// echo "Conectado correctamente"; // (opcional)
?>
