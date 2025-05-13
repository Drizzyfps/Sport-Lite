<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sport Lite</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="contenedor-login-sportlite">
        <div class="tarjeta-login-sportlite">
            <h2 class="titulo-login-sportlite">Iniciar Sesión en Sport Lite</h2>

            <?php
            if (isset($_GET['error'])) {
                echo '<div class="mensaje-error-login">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            if (isset($_GET['success'])) {
                echo '<div class="mensaje-exito-login">' . htmlspecialchars($_GET['success']) . '</div>';
            }
            ?>

            <form class="formulario-login-sportlite" action="procesar_login.php" method="POST">
                <div class="campo-login-sportlite">
                    <label for="email_login" class="etiqueta-login-sportlite">Correo Electrónico:</label>
                    <input type="email" id="email_login" name="email_login" class="input-login-sportlite" required>
                </div>
                <div class="campo-login-sportlite">
                    <label for="contrasena_login" class="etiqueta-login-sportlite">Contraseña:</label>
                    <input type="password" id="contrasena_login" name="contrasena_login" class="input-login-sportlite" required>
                </div>
                <button type="submit" class="boton-login-sportlite">Iniciar Sesión</button>
            </form>
            <p class="olvido-contrasena-login"><a href="restablecer_contrasena.php" class="enlace-olvido-contrasena-login">¿Olvidaste tu contraseña?</a></p>
            <div class="opciones-login-sportlite">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <button class="boton-google-login-sportlite">
                        <img src="img/google_icon.png" alt="Iniciar sesión con Google" class="icono-google-login-sportlite">
                        Google
                    </button>
                    <p class="separador-login-sportlite"><span>O</span></p>
                    <button class="boton-facebook-login-sportlite">
                        <img src="img/facebook_icon.png" alt="Iniciar sesión con Facebook" class="icono-facebook-login-sportlite">
                        Facebook
                    </button>
                </div>
            </div>
            <p class="registro-login-sportlite">¿No tienes cuenta? <a href="registro.php" class="enlace-registro-login-sportlite">Regístrate</a></p>
        </div>
    </div>
</body>
</html>