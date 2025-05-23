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
                    <div id="boton-google-registro"></div>
                    <p class="separador-registro-sportlite"><span>O</span></p>
                    <button id="boton-facebook-registro" class="boton-facebook-registro-sportlite" onclick="manejarInicioSesionFacebook()">
                        <img src="img/facebook_icon.png" alt="Registrarse con Facebook" class="icono-facebook-registro-sportlite">
                        Facebook
                    </button>
                </div>
            </div>
            <p class="login-registro-sportlite">¿Ya tienes cuenta? <a href="login.php" class="enlace-login-registro-sportlite">Iniciar Sesión</a></p>
        </div>
    </div>

    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/es_ES/sdk.js#xfbml=1&version=v13.0&appId=TU_APP_ID_DE_FACEBOOK&autoLogAppEvents=1" nonce=""></script>
    <script src="https://accounts.google.com/gsi/client"></script>
    <script>
        window.fbAsyncInit = function() {
            FB.init({
                appId      : '2064069124097642',
                cookie     : true,
                xfbml      : true,
                version    : 'v13.0'
            });

            FB.AppEvents.logPageView();
        };

        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        function manejarInicioSesionFacebook() {
            FB.login(function(response) {
                if (response.authResponse) {
                    console.log('Bienvenido! Obteniendo información.... ');
                    FB.api('/me?fields=name,email', function(user) {
                        console.log(user);
                        enviarDatosBackend({
                            provider: 'facebook',
                            id: user.id,
                            name: user.name,
                            email: user.email
                        });
                    });
                } else {
                    console.log('El usuario canceló el inicio de sesión o no autorizó la aplicación.');
                }
            }, {scope: 'email'}); // solicitamos los permisos de nombre y correo electrónico
        }


        function enviarDatosBackend(userData) {
            fetch('procesar_registro_social.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(userData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php'; // Redirige a la página principal si el inicio de sesión es exitoso
                } else {
                    alert('Error al iniciar sesión: ' + data.message); // Muestra un mensaje de error
                }
            })
            .catch(error => {
                console.error('Error de red:', error);
            });
        }

    </script>
    <script>
        var client_id = "468632412888-f7igeh3ovnnfg5k7banibnpaahk547lm.apps.googleusercontent.com";
        var scope = "https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email";
        var redirect_uri = "http://localhost/sportlite/procesar_registro_social.php"; 
        var response_type = "code";

        // Función para generar una cadena aleatoria para el estado CSRF
        function generateCSRFState() {
            var length = 24;
            var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            var state = "";
            for (var i = 0, n = charset.length; i < length; ++i) {
                state += charset.charAt(Math.floor(Math.random() * n));
            }
            return state;
        }

        function signInWithGoogle() {
            var state = generateCSRFState();
            localStorage.setItem('google_auth_state', state); // Guarda el estado para verificarlo después

            var googleAuthUrl =
                "https://accounts.google.com/o/oauth2/v2/auth" +
                "?client_id=" + client_id +
                "&scope=" + encodeURIComponent(scope) +
                "&redirect_uri=" + encodeURIComponent(redirect_uri) +
                "&response_type=" + response_type +
                "&state=" + state;

            window.location.href = googleAuthUrl;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const googleSignInButton = document.getElementById('boton-google-registro');
            if (googleSignInButton) {
                googleSignInButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    signInWithGoogle();
                });
            } else {
                console.error('No se encontró el elemento con el ID "boton-google-registro"');
            }
            // Inicialización de Google Sign-In
            google.accounts.id.initialize({
                client_id: client_id, 
                callback: handleGoogleSignIn,
            });

            google.accounts.id.renderButton(
                document.getElementById('boton-google-registro'), 
                { theme: 'filled', size: 'large' }  
            );

            function handleGoogleSignIn(response) {
                const credential = response.credential;
                const decodedToken = JSON.parse(atob(credential.split('.')[1]));
                enviarDatosBackend({
                    provider: 'google',
                    id: decodedToken.sub, 
                    name: decodedToken.name,
                    email: decodedToken.email
                });
            }
        });
    </script>
</body>
</html>
