<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre_registro'];
    $email = $_POST['email_registro'];
    $contrasena = $_POST['contrasena_registro'];
    $confirmar_contrasena = $_POST['confirmar_contrasena_registro'];

    require 'conexion.php';

    if ($conn) {
        if (empty($nombre) || empty($email) || empty($contrasena) || empty($confirmar_contrasena)) {
            header("Location: registro.php?error=" . urlencode("Todos los campos son obligatorios."));
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: registro.php?error=" . urlencode("El formato del correo electrónico no es válido."));
            exit();
        }

        if (strlen($contrasena) < 6) {
            header("Location: registro.php?error=" . urlencode("La contraseña debe tener al menos 6 caracteres."));
            exit();
        }

        if ($contrasena !== $confirmar_contrasena) {
            header("Location: registro.php?error=" . urlencode("Las contraseñas no coinciden."));
            exit();
        }

        try {
            // Verificar si el correo electrónico ya existe
            $stmt_check = $conn->prepare("SELECT email FROM usuarios WHERE email = :email");
            $stmt_check->bindParam(':email', $email);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                header("Location: registro.php?error=" . urlencode("Este correo electrónico ya está registrado."));
                exit();
            }

            // **Seguridad:** Hash de la contraseña antes de guardarla
            $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

            // Insertar el nuevo usuario en la base de datos
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (nombre, email, contrasena) VALUES (:nombre, :email, :contrasena)");
            $stmt_insert->bindParam(':nombre', $nombre);
            $stmt_insert->bindParam(':email', $email);
            $stmt_insert->bindParam(':contrasena', $hashed_password);

            if ($stmt_insert->execute()) {
                // Registro exitoso, redirigir al login con un mensaje de éxito
                header("Location: login.php?success=" . urlencode("Registro exitoso. ¡Inicia sesión!"));
                exit();
            } else {
                header("Location: registro.php?error=" . urlencode("Error al registrar el usuario. Inténtalo de nuevo."));
                exit();
            }

        } catch (PDOException $e) {
            echo "Error al interactuar con la base de datos: " . $e->getMessage();
        }

        $conn = null; // Cerrar la conexión
    } else {
        header("Location: registro.php?error=" . urlencode("Error al conectar con la base de datos."));
        exit();
    }

} else {
    // Si se intenta acceder a este archivo por GET
    header("Location: registro.php");
    exit();
}
?>