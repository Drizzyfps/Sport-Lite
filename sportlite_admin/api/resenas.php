<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

if ($method === 'GET') {
    $sql = "SELECT * FROM reviews ORDER BY date DESC, id DESC";
    $result = $conn->query($sql);
    $reviews = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $row['id'] = intval($row['id']);
            $row['userId'] = intval($row['userId']);
            $row['rating'] = intval($row['rating']);
            $reviews[] = $row;
        }
    }
    $response = $reviews;
} elseif ($method === 'POST') { // Usamos POST para la actualización de estado
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'updateStatus' && isset($data['id'])) {
        $id = intval($data['id']);
        $status = $conn->real_escape_string($data['status']);

        $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Estado de la reseña actualizado.', 'id' => $id];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ['status' => 'error', 'message' => 'Acción o ID no válidos para actualizar estado de reseña.'];
    }
} elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Reseña eliminada.'];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ['status' => 'error', 'message' => 'ID de reseña inválido.'];
    }
}
// No se implementa creación de reseñas desde el panel de admin.

echo json_encode($response);
$conn->close();
?>