<?php
include 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$resultado = $conn->query("SELECT * FROM noticias WHERE id = $id LIMIT 1");

if ($resultado && $resultado->num_rows > 0) {
  $noticia = $resultado->fetch_assoc();
} else {
  die("Noticia no encontrada.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title><?= $noticia['titulo'] ?> | Sport Lite</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="icon" href="img/isotipo.png" type="image/png" />
</head>
<body>

  <!-- ENCABEZADO -->
  <header class="header">
    <div class="logo">
      <a href="index.php"><img src="img/logo.png" alt="Sport Lite Logo" /></a>
    </div>
    <nav>
      <ul>
        <li><a href="index.php">Inicio</a></li>
        <li><a href="#">Reservas</a></li>
        <li><a href="#">Torneos</a></li>
        <li><a href="#">Mi Cuenta</a></li>
      </ul>
    </nav>
  </header>

  <!-- NOTICIA -->
  <section class="detalle-noticia">
    <img src="img/<?= $noticia['imagen'] ?>" alt="<?= $noticia['titulo'] ?>" class="noticia-imagen">
    <h1><?= $noticia['titulo'] ?></h1>
    <p class="fecha">📅 Publicado el <?= date('d/m/Y', strtotime($noticia['fecha_publicacion'])) ?></p>
    <p class="descripcion"><?= nl2br($noticia['descripcion']) ?></p>
    <a href="index.php" class="btn">← Volver al inicio</a>
  </section>

  <!-- FOOTER -->
  <footer>
    <img src="img/imalogotipo.png" alt="Imagotipo Sport Lite" class="footer-logo" />
    <p>© 2025 Sport Lite. Todos los derechos reservados.</p>
  </footer>

</body>
</html>
