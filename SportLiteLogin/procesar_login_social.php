<?php
session_start();
require 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

// Agregar esto para ver los datos recibidos
error_log("procesar_login_social.php: Datos recibidos: " . print_r($data, true));

if ($data && isset($data['provider']) && isset($data['id'])) {
    $provider = $data['provider'];
    $socialId = $data['id'];
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';

    $providerColumn = $provider . '_id';

    $stmt = $conn->prepare("SELECT id, nombre, email FROM usuarios WHERE $providerColumn = ?");
    $stmt->execute([$socialId]);
    $user = $stmt->fetch();

    if ($user) {
        // Usuario encontrado, iniciar sesión
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['correo_usuario'] = $user['email'];
        error_log("procesar_login_social.php: Usuario encontrado, sesión iniciada: " . print_r($_SESSION, true)); // Ver variables de sesión
        echo json_encode(['success' => true]);
    } else {
        // Usuario no encontrado, intentar registrar
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $existingUserByEmail = $stmt->fetch();

        if ($existingUserByEmail) {
            // Correo electrónico ya registrado, vincular cuenta social
            $updateStmt = $conn->prepare("UPDATE usuarios SET $providerColumn = ? WHERE id = ?");
            if ($updateStmt->execute([$socialId, $existingUserByEmail['id']])) {
                // Iniciar sesión después de vincular
                $stmt = $conn->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
                $stmt->execute([$existingUserByEmail['id']]);
                $user = $stmt->fetch();
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['correo_usuario'] = $user['email'];
                error_log("procesar_login_social.php: Cuenta vinculada, sesión iniciada: " . print_r($_SESSION, true));
                echo json_encode(['success' => true, 'message' => 'Cuenta vinculada e inicio de sesión exitoso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al vincular la cuenta.']);
            }
        } else {
            // Registrar nuevo usuario
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, $providerColumn, contrasena) VALUES (?, ?, ?, '')"); 
            if ($stmt->execute([$name, $email, $socialId])) {
                $userId = $conn->lastInsertId();
                $_SESSION['usuario_id'] = $userId;
                $_SESSION['usuario_nombre'] = $name;
                $_SESSION['correo_usuario'] = $email;
                error_log("procesar_login_social.php: Nuevo usuario registrado, sesión iniciada: " . print_r($_SESSION, true));
                echo json_encode(['success' => true, 'message' => 'Registro e inicio de sesión exitoso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario.']);
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
}
?>
