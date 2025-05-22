<?php
include '../conexion.php';

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$slug = $_POST['slug'];
$id_creador = 1;

$imagen = $_FILES['imagen']['name'];
$tmp = $_FILES['imagen']['tmp_name'];
move_uploaded_file($tmp, "../img/" . $imagen);

// Preparamos respuesta por defecto
$mensaje = "";
$exito = true;

try {
  $sql = "INSERT INTO torneos (titulo, descripcion, imagen, slug, id_creador) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssi", $titulo, $descripcion, $imagen, $slug, $id_creador);
  $stmt->execute();
  $mensaje = "✅ Torneo guardado correctamente.";
} catch (mysqli_sql_exception $e) {
  $exito = false;
  if (str_contains($e->getMessage(), 'Duplicate entry')) {
    $mensaje = "⚠️ Ya existe un torneo con ese <strong>slug</strong>. Usa uno diferente.";
  } else {
    $mensaje = "❌ Error al guardar el torneo: <br><small>" . $e->getMessage() . "</small>";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Resultado | Sport Lite</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="icon" href="../img/isotipo.png" type="image/png" />
  <style>
    .confirmacion {
      max-width: 500px;
      margin: 100px auto;
      text-align: center;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .confirmacion img {
      width: 60px;
      margin-bottom: 20px;
    }
    .confirmacion h2 {
      color: <?= $exito ? '#3c763d' : '#b30000' ?>;
      margin-bottom: 10px;
    }
    .confirmacion a {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: white;
      background-color: var(--azul);
      padding: 10px 20px;
      border-radius: 8px;
    }
  </style>
</head>
<body>

  <div class="confirmacion">
    <img src="../img/isotipo.png" alt="Sport Lite" />
    <h2><?= $mensaje ?></h2>
    <a href="<?= $exito ? '../index.php' : 'agregar_torneo.php' ?>">Volver</a>
  </div>

</body>
</html>
<?php