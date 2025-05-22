<?php
session_start(); // Por si en el futuro quieres validar sesión
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Agregar Torneo | Sport Lite</title>
  <link rel="stylesheet" href="../css/style.css" />
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

  <!-- FORMULARIO -->
<main class="contenedor-formulario">
  <div class="tarjeta-formulario">
    <h2>🎯 Agregar Nuevo Torneo</h2>
    <form action="guardar_torneo.php" method="POST" enctype="multipart/form-data">
      <label for="titulo">Título del torneo</label>
      <input type="text" name="titulo" id="titulo" required placeholder="Ej: Torneo F5 Relámpago" />

      <label for="descripcion">Descripción</label>
      <textarea name="descripcion" id="descripcion" rows="5" required placeholder="Breve descripción del torneo"></textarea>

      <label for="imagen">Imagen</label>
      <input type="file" name="imagen" id="imagen" accept="image/*" required />

      <label for="slug">Slug (URL amigable sin espacios)</label>
      <input type="text" name="slug" id="slug" required placeholder="Ej: torneo-f5-relampago" />

      <button type="submit" class="boton-guardar">Guardar Torneo</button>
    </form>
  </div>
</main>

  <!-- FOOTER -->
  <footer>
    <img src="../img/imalogotipo.png" alt="Imagotipo Sport Lite" class="footer-logo" />
    <p>© 2025 Sport Lite. Todos los derechos reservados.</p>
  </footer>

</body>
</html>
