<?php include 'conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sport Lite | Inicio</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="icon" href="img/isotipo.png" type="image/png" />
</head>
<body>

  <!-- ENCABEZADO -->
  <header class="header">
    <div class="logo">
      <img src="img/logo.png" alt="Sport Lite Logo" />
    </div>
    <nav>
      <ul>
        <li><a href="#">Inicio</a></li>
        <li><a href="#">Reservas</a></li>
        <li><a href="#">Torneos</a></li>
        <li><a href="#">Mi Cuenta</a></li>
      </ul>
    </nav>
  </header>

  <!-- HERO -->
  <section class="hero">
    <img src="img/isotipo.png" alt="Sport Lite Icon" class="hero-logo" />
    <h1>Bienvenido a <span class="resaltado">Sport Lite</span></h1>
    <p>Reserva canchas, únete a torneos y vive el deporte con energía.</p>
    <a href="#" class="btn">Explorar Torneos</a>
  </section>

  <!-- NOTICIAS -->
  <section class="noticias">
    <h2 class="titulo-centro">Últimas Noticias</h2>
    <div class="card-container">
      <?php
        $noticias = $conn->query("SELECT * FROM noticias ORDER BY fecha_publicacion DESC LIMIT 4");
        while ($n = $noticias->fetch_assoc()):
      ?>
        <a href="noticia.php?id=<?= $n['id'] ?>" class="card">
  <img src="img/<?= $n['imagen'] ?>" alt="<?= $n['titulo'] ?>" />
  <h3><?= $n['titulo'] ?></h3>
  <p><?= $n['descripcion'] ?></p>
        </a>
      <?php endwhile; ?>
    </div>
  </section>

   <!-- TORNEOS ACTIVOS -->
<section class="torneos">
  <h2 class="titulo-centro">Torneos Activos</h2>
  <div class="card-container">
    <?php
      include 'conexion.php';
      $torneos = $conn->query("SELECT * FROM torneos ORDER BY fecha_creacion DESC");

      while ($t = $torneos->fetch_assoc()):
    ?>
      <a href="torneo.php?slug=<?= $t['slug'] ?>" class="card">
        <img src="img/<?= $t['imagen'] ?>" alt="<?= $t['titulo'] ?>" />
        <h3><?= $t['titulo'] ?></h3>
        <p><?= $t['descripcion'] ?></p>
      </a>
    <?php endwhile; ?>
  </div>
</section>

  </section>

  <!-- FOOTER -->
  <footer>
    <img src="img/imalogotipo.png" alt="Imagotipo Sport Lite" class="footer-logo" />
    <p>© 2025 Sport Lite. Todos los derechos reservados.</p>
  </footer>

</body>
</html>
