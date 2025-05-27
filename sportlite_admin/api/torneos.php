<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

if ($method === 'GET') {
    $sql = "SELECT * FROM tournaments ORDER BY id DESC";
    $result = $conn->query($sql);
    $tournaments = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Convertir tipos de datos si es necesario (ej. enteros)
            $row['id'] = intval($row['id']);
            $row['teams'] = intval($row['teams']);
            $row['matchesPlayed'] = intval($row['matchesPlayed']);
            $row['goalsScored'] = intval($row['goalsScored']);
            $tournaments[] = $row;
        }
    }
    $response = $tournaments;
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Verificar si es una actualización de estadísticas o creación/actualización de torneo
    if (isset($data['action']) && $data['action'] === 'updateStats') {
        $id = intval($data['id']);
        $matchesPlayed = intval($data['matchesPlayed']);
        $goalsScored = intval($data['goalsScored']);

        $stmt = $conn->prepare("UPDATE tournaments SET matchesPlayed = ?, goalsScored = ? WHERE id = ?");
        $stmt->bind_param("iii", $matchesPlayed, $goalsScored, $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Estadísticas del torneo actualizadas.', 'id' => $id];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } elseif (isset($data['id'])) { // Actualización de torneo
        $id = intval($data['id']);
        $name = $conn->real_escape_string($data['name']);
        $status = $conn->real_escape_string($data['status']);
        $teams = intval($data['teams']);
        $currentPhase = $conn->real_escape_string($data['currentPhase']);
        // matchesPlayed y goalsScored no se actualizan aquí directamente, sino via 'updateStats'

        $stmt = $conn->prepare("UPDATE tournaments SET name = ?, status = ?, teams = ?, currentPhase = ? WHERE id = ?");
        $stmt->bind_param("ssisi", $name, $status, $teams, $currentPhase, $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Torneo actualizado.', 'id' => $id];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } else { // Creación de nuevo torneo
        $name = $conn->real_escape_string($data['name']);
        $status = $conn->real_escape_string($data['status']);
        $teams = intval($data['teams']);
        $currentPhase = $conn->real_escape_string($data['currentPhase']);
        // Los valores iniciales para matchesPlayed y goalsScored son 0
        $matchesPlayed = 0;
        $goalsScored = 0;

        $stmt = $conn->prepare("INSERT INTO tournaments (name, status, teams, matchesPlayed, goalsScored, currentPhase) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiis", $name, $status, $teams, $matchesPlayed, $goalsScored, $currentPhase);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Torneo creado.', 'id' => $stmt->insert_id];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    }
} elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM tournaments WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Torneo eliminado.'];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ['status' => 'error', 'message' => 'ID de torneo inválido.'];
    }
}

echo json_encode($response);
$conn->close();
?>