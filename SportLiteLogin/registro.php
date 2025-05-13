<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Sport Lite</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="registro-contenedor-sportlite">
        <div class="tarjeta-registro-sportlite">
            <h2 class="titulo-registro-sportlite">Regístrate en Sport Lite</h2>

            <?php
            if (isset($_GET['error'])) {
                echo '<div class="mensaje-error-registro">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>

            <form class="formulario-registro-sportlite" action="procesar_registro.php" method="POST">
                <div class="campo-registro-sportlite">
                    <label for="nombre_registro" class="etiqueta-registro-sportlite">Nombre:</label>
                    <input type="text" id="nombre_registro" name="nombre_registro" class="input-registro-sportlite" required>
                </div>
                <div class="campo-registro-sportlite">
                    <label for="email_registro" class="etiqueta-registro-sportlite">Correo Electrónico:</label>
                    <input type="email" id="email_registro" name="email_registro" class="input-registro-sportlite" required>
                </div>
                <div class="campo-registro-sportlite">
                    <label for="contrasena_registro" class="etiqueta-registro-sportlite">Contraseña:</label>
                    <input type="password" id="contrasena_registro" name="contrasena_registro" class="input-registro-sportlite" required>
                </div>
                <div class="campo-registro-sportlite">
                    <label for="confirmar_contrasena_registro" class="etiqueta-registro-sportlite">Confirmar Contraseña:</label>
                    <input type="password" id="confirmar_contrasena_registro" name="confirmar_contrasena_registro" class="input-registro-sportlite" required>
                </div>
                <button type="submit" class="boton-registro-sportlite">Registrarse</button>
            </form>
            <div class="opciones-registro-sportlite">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <button class="boton-google-registro-sportlite">
                        <img src="img/google_icon.png" alt="Registrarse con Google" class="icono-google-registro-sportlite">
                        Google
                    </button>
                    <p class="separador-registro-sportlite"><span>O</span></p>
                    <button class="boton-facebook-registro-sportlite">
                        <img src="img/facebook_icon.png" alt="Registrarse con Facebook" class="icono-facebook-registro-sportlite">
                        Facebook
                    </button>
                </div>
            </div>
            <p class="login-registro-sportlite">¿Ya tienes cuenta? <a href="login.php" class="enlace-login-registro-sportlite">Iniciar Sesión</a></p>
        </div>
    </div>
</body>
</html>