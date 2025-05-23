<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Sport Lite</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="reset-password-container">
        <h2 class="reset-password-title">Nueva Contraseña</h2>
        <p class="reset-password-description">Ingresa tu nueva contraseña y confírmala.</p>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="success-message">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        ?>
        <form class="reset-password-form" action="procesar_nueva_contrasena.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <div class="form-group">
                <label for="nueva_contrasena" class="form-label">Nueva Contraseña:</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="confirmar_nueva_contrasena" class="form-label">Confirmar Nueva Contraseña:</label>
                <input type="password" id="confirmar_nueva_contrasena" name="confirmar_nueva_contrasena" class="form-input" required>
            </div>
            <button type="submit" class="reset-password-button">Guardar Nueva Contraseña</button>
        </form>
        <div class="reset-password-link" style="margin-top: 20px;">
            <a href="login.php">Volver al inicio de sesión</a>
        </div>
    </div>
</body>
</html>