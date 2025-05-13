<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener información del usuario de la sesión
$nombre_usuario = $_SESSION['usuario_nombre'];
$email_usuario = $_SESSION['usuario_email'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportLite - Página Principal</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="sl-contenedor-principal">
        <header class="sl-header">
            <h1>Bienvenido a SportLite</h1>
            <p>Usuario: <?php echo htmlspecialchars($nombre_usuario); ?> (<?php echo htmlspecialchars($email_usuario); ?>)</p>
            <p><a href="logout.php" class="sl-enlace-logout">Cerrar Sesión</a></p>
        </header>
        <main class="sl-main-contenido">
            <p>¡Has iniciado sesión exitosamente!</p>
            </main>
        <footer class="sl-footer">
            <p>&copy; 2025 SportLite</p>
        </footer>
    </div>
</body>
</html>