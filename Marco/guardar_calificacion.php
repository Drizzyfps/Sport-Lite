<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

$id_usuario = $_SESSION['usuario']['id'];
$categoria = $_POST['categoria'];
$estrellas = $_POST['estrellas'];
$comentario = $_POST['comentario'];

$stmt = $conn->prepare("INSERT INTO calificaciones (id_usuario, categoria, estrellas, comentario) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isis", $id_usuario, $categoria, $estrellas, $comentario);

if ($stmt->execute()) {
  header("Location: index.php?mensaje=calificacion_ok");
} else {
  echo "Error al guardar: " . $stmt->error;
}
