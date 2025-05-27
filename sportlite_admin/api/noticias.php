<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

if ($method === 'GET') {
    $sql = "SELECT * FROM news ORDER BY publishedDate DESC, id DESC";
    $result = $conn->query($sql);
    $newsItems = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $row['id'] = intval($row['id']);
            $newsItems[] = $row;
        }
    }
    $response = $newsItems;
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'])) { // Actualizar noticia
        $id = intval($data['id']);
        $title = $conn->real_escape_string($data['title']);
        $content = $conn->real_escape_string($data['content']);
        $category = $conn->real_escape_string($data['category']);
        $publishedDate = $conn->real_escape_string($data['publishedDate']);
        $author = $conn->real_escape_string($data['author']);

        $stmt = $conn->prepare("UPDATE news SET title = ?, content = ?, category = ?, publishedDate = ?, author = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $title, $content, $category, $publishedDate, $author, $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Noticia actualizada.', 'id' => $id];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } else { // Crear nueva noticia
        $title = $conn->real_escape_string($data['title']);
        $content = $conn->real_escape_string($data['content']);
        $category = $conn->real_escape_string($data['category']);
        $publishedDate = $conn->real_escape_string($data['publishedDate']); // JS ya envía la fecha formateada
        $author = $conn->real_escape_string($data['author']);

        $stmt = $conn->prepare("INSERT INTO news (title, content, category, publishedDate, author) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $content, $category, $publishedDate, $author);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Noticia creada.', 'id' => $stmt->insert_id];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    }
} elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Noticia eliminada.'];
        } else {
            $response = ['status' => 'error', 'message' => $stmt->error];
        }
        $stmt->close();
    } else {
        $response = ['status' => 'error', 'message' => 'ID de noticia inválido.'];
    }
}

echo json_encode($response);
$conn->close();
?>