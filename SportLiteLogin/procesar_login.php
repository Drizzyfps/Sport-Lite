<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email_login']);
    $contrasena = trim($_POST['contrasena_login']);

    // Incluir el archivo de conexión a la base de datos
    require 'conexion.php';

    if ($conn) {
        try {
            // Verificar si el usuario existe por su correo electrónico
            $stmt = $conn->prepare("SELECT id, nombre, email, contrasena FROM usuarios WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Verificar la contraseña
                if (password_verify($contrasena, $usuario['contrasena'])) {
                    // Inicio de sesión exitoso
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    header("Location: index.php"); // Redirigir a la página principal
                    exit();
                } else {
                    // Contraseña incorrecta
                    header("Location: login.php?error=" . urlencode("Correo electrónico o contraseña incorrectos."));
                    exit();
                }
            } else {
                // Usuario no encontrado
                header("Location: login.php?error=" . urlencode("Correo electrónico o contraseña incorrectos."));
                exit();
            }

        } catch (PDOException $e) {
            echo "Error al consultar la base de datos: " . $e->getMessage();
        }

        $conn = null; // Cerrar la conexión
    } else {
        header("Location: login.php?error=" . urlencode("Error al conectar con la base de datos."));
        exit();
    }

} else {
    // Si se intenta acceder a este archivo por GET
    header("Location: login.php");
    exit();
}
?>