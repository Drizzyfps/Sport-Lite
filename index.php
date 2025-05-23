<?php include 'conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sport Lite | Inicio</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="icon" href="img/isotipo1.png" type="image/png" />
</head>
<body>

  <!-- ENCABEZADO -->
  <header class="header">
    <div class="logo">
      <img src="img/logo1.png" alt="Sport Lite Logo" />
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

<?php if (isset($_GET['mensaje'])): ?>
  <div id="mensaje-exito" class="mensaje-exito">
    <?= htmlspecialchars($_GET['mensaje']) ?>
    <span class="cerrar-mensaje" onclick="cerrarMensaje()">×</span>
  </div>
<?php endif; ?>

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
   <!-- CALIFICACIONES -->
<section class="calificaciones">
  <h2 class="titulo-centro">⭐ Califícanos</h2>
  <form action="guardar_calificacion.php" method="POST" class="formulario-vertical">
    <label for="categoria">¿Qué deseas calificar?</label>
    <select name="categoria" required>
      <option value="Atención">Atención</option>
      <option value="Canchas">Canchas</option>
      <option value="Torneos">Torneos</option>
    </select>

    <label for="estrellas">Calificación (1 a 5)</label>
    <input type="number" name="estrellas" min="1" max="5" required>

    <label for="comentario">Comentario</label>
    <textarea name="comentario" rows="3" required></textarea>

    <button type="submit" class="btn">Enviar Calificación</button>
  </form>
</section>


   <!-- RESEÑAS -->
<section class="reseñas">
  <h2 class="titulo-centro">Últimas Reseñas</h2>
  <div class="card-container">
    <?php
      $res = $conn->query("
        SELECT categoria, estrellas, comentario, fecha
        FROM calificaciones
        ORDER BY fecha DESC LIMIT 5
      ");

      while ($r = $res->fetch_assoc()):
    ?>
    <div class="card">
      <h3>🗂 <?= $r['categoria'] ?></h3>
      <p><?= str_repeat("⭐", $r['estrellas']) ?></p>
      <p>"<?= $r['comentario'] ?>"</p>
      <small><?= date("d/m/Y", strtotime($r['fecha'])) ?></small>
    </div>
    <?php endwhile; ?>
  </div>
</section>

<script>
function cerrarMensaje() {
  const mensaje = document.getElementById("mensaje-exito");
  if (mensaje) mensaje.remove();
}

// Auto eliminar después de 4 segundos
setTimeout(() => {
  cerrarMensaje();
}, 4000);
</script>

<!-- SECCIÓN CANCHA -->
<section class="seccion-ubicacion">
  <div class="contenedor-ubicacion">
    
    <!-- Columna izquierda -->
    <div class="info-cancha">
      <h2>📍 Ubicación</h2>
      <p><strong>Dirección:</strong> Vereda Guabinal, km 5 vía Girardot – Tocaima</p>
      <p><strong>Horarios:</strong> Lunes a Domingo de 7:00 a.m. a 10:00 p.m.</p>
      <p><strong>Contacto:</strong> WhatsApp <a href="https://wa.me/573217005069" target="_blank">321 700 5069</a></p>
      <a href="https://www.google.com/maps?q=4.3534054,-74.8019362" target="_blank" class="boton-mapa">📍 Cómo llegar</a>
    </div>

    <!-- Columna derecha -->
    <div class="mapa-cancha">
      <iframe
        width="100%"
        height="300"
        style="border:0; border-radius: 10px"
        loading="lazy"
        allowfullscreen
        referrerpolicy="no-referrer-when-downgrade"
        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3978.5296655058167!2d-74.8019362!3d4.3534054!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNCDCsDIxJzEyLjMiTiA3NMKwNDgnMDcuMCJX!5e0!3m2!1ses!2sco!4v1715700000000">
      </iframe>
    </div>

  </div>
</section>


  <!-- FOOTER -->
  <footer>
    <img src="img/imalogotipo.png" alt="Imagotipo Sport Lite" class="footer-logo" />
    <p>© 2025 Sport Lite. Todos los derechos reservados.</p>
  </footer>

</body>
</html>
