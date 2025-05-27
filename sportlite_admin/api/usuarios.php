<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

if ($method === 'GET') {
    $sql = "SELECT id, name, email, role, registrationDate, lastLogin FROM users ORDER BY registrationDate DESC, id DESC";
    $result = $conn->query($sql);
    $users = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $row['id'] = intval($row['id']);
            $users[] = $row;
        }
    }
    $response = $users;
} elseif ($method === 'POST') { // Usamos POST para la actualización de rol
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'updateRole' && isset($data['id'])) {
        $id = intval($data['id']);
        $role = $conn->real_escape_string($data['role']);

        // Actualizar también lastLogin podría ser una opción aquí si se considera una "acción administrativa"
        // Por ahora, solo actualizamos el rol.
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Rol del usuario actualizado.', 'id' => $id];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ['status' => 'error', 'message' => 'Acción o ID no válidos para actualizar rol de usuario.'];
    }
}
// No se implementa creación o eliminación de usuarios directamente desde esta simple API de gestión.

echo json_encode($response);
$conn->close();
?>