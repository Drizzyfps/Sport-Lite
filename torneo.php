<?php
include 'conexion.php';

// Verificamos que haya un "slug" en la URL
if (!isset($_GET['slug'])) {
  echo "Torneo no especificado.";
  exit;
}

$slug = $_GET['slug'];

// Consultamos el torneo desde la base de datos
$stmt = $conn->prepare("SELECT * FROM torneos WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
  echo "Torneo no encontrado.";
  exit;
}

$torneo = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title><?= $torneo['titulo'] ?> | Sport Lite</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

  <header class="header">
    <div class="logo">
      <a href="index.php"><img src="img/logo.png" alt="Sport Lite Logo" /></a>
    </div>
  </header>

  <main class="torneo-detalle">
  <h1 class="titulo-centro"><?= $torneo['titulo'] ?></h1>
  <div class="torneo-contenido">
    <img src="img/<?= $torneo['imagen'] ?>" alt="<?= $torneo['titulo'] ?>" class="torneo-img" />
    <p class="torneo-descripcion"><?= nl2br($torneo['descripcion']) ?></p>

    <div class="torneo-botones">
      <a href="index.php" class="btn-volver">← Volver al inicio</a>
      <a href="#" class="btn-inscribirse">Inscribirse</a>
    </div>
  </div>
</main>
  
    <footer class="footer">
      <p>&copy; 2023 Sport Lite. Todos los derechos reservados.</p>
    </footer>
  
    <script src="js/script.js"></script>

</body>
</html>
