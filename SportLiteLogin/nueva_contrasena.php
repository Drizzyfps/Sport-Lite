<?php
// Iniciar sesión para mensajes flash
session_start();

// Verificar si el token está presente en la URL
if (!isset($_GET['token'])) {
    $_SESSION['error'] = 'Enlace de restablecimiento inválido.';
    header('Location: login.php');
    exit();
}

$token = $_GET['token'];

// Incluir archivo de conexión a la base de datos
require 'conexion.php'; // ¡CORRECCIÓN: Cambiado a 'conexion.php'!

// Verificar si el token existe en la base de datos y no ha expirado
try {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE token_restablecimiento = :token AND fecha_expiracion_token > NOW()");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION['error'] = 'El enlace de restablecimiento es inválido o ha expirado.';
        header('Location: login.php');
        exit();
    }

    // Si el token es válido, mostrar el formulario para la nueva contraseña
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error de base de datos: ' . $e->getMessage();
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Sport Lite</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="contenedor-nueva-contrasena">
        <div class="tarjeta-nueva-contrasena">
            <h2 class="titulo-nueva-contrasena">Nueva Contraseña</h2>
            <p class="instrucciones-nueva-contrasena">Ingresa tu nueva contraseña y confírmala.</p>
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="mensaje-error">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="mensaje-exito">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>
            <form class="formulario-nueva-contrasena" action="procesar_nueva_contrasena.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="campo-nueva-contrasena">
                    <label for="nueva_contrasena" class="etiqueta-nueva-contrasena">Nueva Contraseña:</label>
                    <input type="password" id="nueva_contrasena" name="nueva_contrasena" class="input-nueva-contrasena" required>
                </div>
                <div class="campo-nueva-contrasena">
                    <label for="confirmar_nueva_contrasena" class="etiqueta-nueva-contrasena">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirmar_nueva_contrasena" name="confirmar_nueva_contrasena" class="input-nueva-contrasena" required>
                </div>
                <button type="submit" class="boton-nueva-contrasena">Guardar Nueva Contraseña</button>
            </form>
            <p class="enlace-volver-login"><a href="login.php">Volver al inicio de sesión</a></p>
        </div>
    </div>
</body>
</html>