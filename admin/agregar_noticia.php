<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Noticia | Sport Lite</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="icon" href="../img/isotipo.png" type="image/png" />
</head>
<body>

  <!-- ENCABEZADO -->
  <header class="header">
    <div class="logo">
      <a href="../index.php"><img src="../img/logo.png" alt="Sport Lite Logo" /></a>
    </div>
    <nav>
      <ul>
        <li><a href="../index.php">Inicio</a></li>
        <li><a href="#">Panel</a></li>
        <li><a href="#">Cerrar Sesión</a></li>
      </ul>
    </nav>
  </header>

  <!-- FORMULARIO AGREGAR NOTICIA -->
  <section class="formulario-contenedor">
  <h2 class="titulo-centro">📰 Publicar Nueva Noticia</h2>

  <form action="guardar_noticia.php" method="POST" enctype="multipart/form-data" class="formulario-vertical">
    <label for="titulo">Título</label>
    <input type="text" name="titulo" id="titulo" placeholder="Título de la noticia" required>

    <label for="descripcion">Descripción</label>
    <textarea name="descripcion" id="descripcion" placeholder="Descripción de la noticia" rows="5" required></textarea>

    <label for="imagen">Imagen</label>
    <input type="file" name="imagen" id="imagen" accept="image/*" required>

    <button type="submit" class="btn">Publicar Noticia</button>
  </form>
</section>


  <!-- FOOTER -->
  <footer>
    <img src="../img/imalogotipo.png" alt="Imagotipo Sport Lite" class="footer-logo" />
    <p>© 2025 Sport Lite. Todos los derechos reservados.</p>
  </footer>

</body>
</html>
