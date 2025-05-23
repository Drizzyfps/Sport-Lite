<?php
session_start();
require 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['provider']) && isset($data['id']) && isset($data['name']) && isset($data['email'])) {
    $provider = $data['provider'];
    $socialId = $data['id'];
    $name = $data['name'];
    $email = $data['email'];

    $providerColumn = $provider . '_id';

    // Verificar si ya existe un usuario con este ID social
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE $providerColumn = ?");
    $stmt->execute([$socialId]);
    $existingUserBySocialId = $stmt->fetch();

    if ($existingUserBySocialId) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una cuenta registrada con este proveedor social.']);
    } else {
        // Verificar si ya existe un usuario con este correo electrónico
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $existingUserByEmail = $stmt->fetch();

        if ($existingUserByEmail) {
            echo json_encode(['success' => false, 'message' => 'Este correo electrónico ya está registrado.']);
        } else {
            // Registrar nuevo usuario
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, $providerColumn, contrasena) VALUES (?, ?, ?, '')");
            if ($stmt->execute([$name, $email, $socialId])) {
                $userId = $conn->lastInsertId();
                $_SESSION['usuario_id'] = $userId;
                $_SESSION['nombre_usuario'] = $name;
                $_SESSION['correo_usuario'] = $email;
                echo json_encode(['success' => true, 'message' => 'Registro e inicio de sesión exitoso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario.']);
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos de registro inválidos.']);
}
?>