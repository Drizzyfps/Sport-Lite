<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sport Lite</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="reset-password-container">
        <h2 class="reset-password-title">Restablecer Contraseña</h2>
        <p class="reset-password-description">Ingresa tu correo electrónico para recibir un enlace para restablecer tu contraseña.</p>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        <form class="reset-password-form" action="procesar_restablecimiento.php" method="POST">
            <div class="form-group">
                <label for="email_restablecer" class="form-label">Correo Electrónico:</label>
                <input type="email" id="email_restablecer" name="email_restablecer" class="form-input" required>
            </div>
            <button type="submit" class="reset-password-button">Enviar Enlace de Restablecimiento</button>
        </form>
        <div class="reset-password-link">
            <a href="login.php">Volver al inicio de sesión</a>
        </div>
    </div>
</body>
</html>