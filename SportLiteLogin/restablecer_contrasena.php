<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sport Lite</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="contenedor-restablecer-contrasena">
        <div class="tarjeta-restablecer-contrasena">
            <h2 class="titulo-restablecer-contrasena">Restablecer Contraseña</h2>
            <p class="instrucciones-restablecer-contrasena">Ingresa tu correo electrónico para recibir un enlace para restablecer tu contraseña.</p>
            <form class="formulario-restablecer-contrasena" action="procesar_restablecimiento.php" method="POST">
                <div class="campo-restablecer-contrasena">
                    <label for="email_restablecer" class="etiqueta-restablecer-contrasena">Correo Electrónico:</label>
                    <input type="email" id="email_restablecer" name="email_restablecer" class="input-restablecer-contrasena" required>
                </div>
                <button type="submit" class="boton-restablecer-contrasena">Enviar Enlace de Restablecimiento</button>
            </form>
            <p class="enlace-volver-login"><a href="login.php">Volver al inicio de sesión</a></p>
        </div>
    </div>
</body>
</html>