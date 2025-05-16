<?php 
include 'conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form_contacto"])) {
    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $mensaje_texto = trim($_POST["mensaje"]);

    if (!empty($nombre) && !empty($email) && !empty($mensaje_texto)) {
        $stmt = $conn->prepare("INSERT INTO contactos (nombre, email, mensaje) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $email, $mensaje_texto);

        if ($stmt->execute()) {
            $mensaje = "<div class='alerta exito'>✅ Mensaje enviado correctamente.</div>";
        } else {
            $mensaje = "<div class='alerta error'>❌ Error al enviar el mensaje.</div>";
        }
    } else {
        $mensaje = "<div class='alerta advertencia'>⚠️ Todos los campos son obligatorios.</div>";
    }
}
?>

<!-- Íconos Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  .contacto-box {
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      max-width: 600px;
      width: 90%;
      margin: 50px auto;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      font-family: 'Segoe UI', sans-serif;
      box-sizing: border-box;
  }

  .contacto-box h2 {
      text-align: center;
      color: #007bff;
      margin-bottom: 15px;
      font-size: 28px;
  }

  .contacto-box p.descripcion {
      text-align: center;
      color: #444;
      margin-bottom: 25px;
      font-size: 15px;
  }

  .input-icon,
  .textarea-icon {
      position: relative;
      margin-top: 10px;
  }

  .input-icon i,
  .textarea-icon i {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      color: #888;
      pointer-events: none;
      font-size: 16px;
  }

  .textarea-icon i {
      top: 16px;
      transform: none;
  }

  .input-icon input,
  .textarea-icon textarea {
      width: 100%;
      padding: 12px 12px 12px 44px;
      border: 1px solid #ccc;
      border-radius: 8px;
      transition: border 0.3s ease, box-shadow 0.3s ease;
      font-size: 15px;
      box-sizing: border-box;
  }

  .input-icon input:focus,
  .textarea-icon textarea:focus {
      border-color: #00ac0e;
      box-shadow: 0 0 5px rgba(0, 172, 14, 0.3);
      outline: none;
  }

  .textarea-icon textarea {
      resize: vertical;
      min-height: 100px;
  }

  .contacto-box button {
      margin-top: 20px;
      padding: 12px;
      width: 100%;
      background-color: rgb(0, 172, 14);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
  }

  .contacto-box button:hover {
      background-color: #009b0f;
  }

  .alerta {
      margin-top: 20px;
      padding: 12px;
      border-radius: 6px;
      text-align: center;
      font-weight: bold;
      font-size: 15px;
  }

  .alerta.exito {
      background-color: #e6ffe6;
      border: 1px solid #00ac0e;
      color: #007d10;
  }

  .alerta.error {
      background-color: #ffe6e6;
      border: 1px solid #e60000;
      color: #b30000;
  }

  .alerta.advertencia {
      background-color: #fffbe6;
      border: 1px solid #ffcc00;
      color: #996600;
  }

  @media (max-width: 500px) {
      .contacto-box {
          padding: 20px;
      }

      .contacto-box h2 {
          font-size: 22px;
      }

      .input-icon i,
      .textarea-icon i {
          left: 8px;
          font-size: 14px;
      }

      .input-icon input,
      .textarea-icon textarea {
          padding-left: 40px;
      }
  }
</style>

<div class="contacto-box">
    <h2>📬 Contáctanos</h2>
    <p class="descripcion">¿Tienes preguntas o comentarios? ¡Estamos aquí para ayudarte!</p>

    <form method="POST">
        <input type="hidden" name="form_contacto" value="1">

        <label for="nombre">Nombre:</label>
        <div class="input-icon">
            <i class="fa fa-user"></i>
            <input type="text" name="nombre" required placeholder="Tu nombre completo">
        </div>

        <label for="email">Email:</label>
        <div class="input-icon">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" required placeholder="Tu correo electrónico">
        </div>

        <label for="mensaje">Mensaje:</label>
        <div class="textarea-icon">
            <i class="fa fa-comment-dots"></i>
            <textarea name="mensaje" rows="5" required placeholder="Escribe tu mensaje aquí"></textarea>
        </div>

        <button type="submit">Enviar</button>

        <?= $mensaje; ?>
    </form>
</div>
