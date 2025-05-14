<?php
include '../conexion.php';

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$img_name = $_FILES['imagen']['name'];
$tmp = $_FILES['imagen']['tmp_name'];
$ruta = "../img/" . $img_name;

move_uploaded_file($tmp, $ruta);

$sql = "INSERT INTO noticias (titulo, descripcion, imagen) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $titulo, $descripcion, $img_name);

$mensaje = "";
$exito = false;

if ($stmt->execute()) {
  $mensaje = "✅ Noticia publicada correctamente.";
  $exito = true;
} else {
  $mensaje = "❌ Error al guardar: " . $stmt->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resultado | Sport Lite</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <section class="resultado-mensaje">
    <div class="caja-resultado <?= $exito ? 'exito' : 'error' ?>">
      <h2><?= $mensaje ?></h2>
      <a href="../index.php" class="btn">← Volver al inicio</a>
    </div>
  </section>

</body>
</html>
