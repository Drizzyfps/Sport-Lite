<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['token']) && isset($_POST['nueva_contrasena']) && isset($_POST['confirmar_nueva_contrasena'])) {
        $token = filter_var($_POST['token'], FILTER_SANITIZE_STRING);
        $nueva_contrasena = $_POST['nueva_contrasena'];
        $confirmar_nueva_contrasena = $_POST['confirmar_nueva_contrasena'];

        require 'conexion.php'; 

        try {
            // Verificar si el token es válido y no ha expirado
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE token_restablecimiento = :token AND fecha_expiracion_token > NOW()");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Verificar si las contraseñas coinciden
                if ($nueva_contrasena === $confirmar_nueva_contrasena) {
                    // Hashear la nueva contraseña de forma segura
                    $contrasena_hasheada = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

                    // Actualizar la contraseña del usuario en la base de datos y eliminar el token
                    $stmt = $conn->prepare("UPDATE usuarios SET contrasena = :contrasena, token_restablecimiento = NULL, fecha_expiracion_token = NULL WHERE id = :id");
                    $stmt->bindParam(':contrasena', $contrasena_hasheada);
                    $stmt->bindParam(':id', $usuario['id']);
                    $stmt->execute();

                    // Mostrar mensaje de éxito y redirigir al inicio de sesión
                    $_SESSION['success'] = 'Tu contraseña ha sido restablecida exitosamente. Ya puedes iniciar sesión.';
                    header('Location: login.php');
                    exit();

                } else {
                    // Las contraseñas no coinciden
                    $_SESSION['error'] = 'Las contraseñas no coinciden.';
                    header('Location: nueva_contrasena.php?token=' . $token);
                    exit();
                }

            } else {
                // El token es inválido o ha expirado
                $_SESSION['error'] = 'El enlace de restablecimiento es inválido o ha expirado.';
                header('Location: login.php');
                exit();
            }

        } catch (PDOException $e) {
            // Error de base de datos
            $_SESSION['error'] = 'Error de base de datos: ' . $e->getMessage();
            header('Location: nueva_contrasena.php?token=' . $token);
            exit();
        }

    } else {
        // Falta información en el formulario
        $_SESSION['error'] = 'Hubo un problema con la solicitud. Intenta de nuevo.';
        header('Location: login.php');
        exit();
    }
} else {
    // Si se intenta acceder al script por GET
    header('Location: login.php');
    exit();
}
?>