<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

if ($method === 'GET') {
    $sql = "SELECT * FROM payments ORDER BY date DESC, id DESC";
    $result = $conn->query($sql);
    $payments = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $row['id'] = intval($row['id']);
            $row['userId'] = intval($row['userId']);
            $row['amount'] = floatval($row['amount']);
            $payments[] = $row;
        }
    }
    $response = $payments;
} elseif ($method === 'POST') { // Usamos POST para la actualización de estado
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'updateStatus' && isset($data['id'])) {
        $id = intval($data['id']);
        $status = $conn->real_escape_string($data['status']);

        $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Estado del pago actualizado.', 'id' => $id];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ['status' => 'error', 'message' => 'Acción o ID no válidos para actualizar estado de pago.'];
    }
}
// No se implementa creación o eliminación de pagos desde el panel de admin directamente en este ejemplo.

echo json_encode($response);
$conn->close();
?>