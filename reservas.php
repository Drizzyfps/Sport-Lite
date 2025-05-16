<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    echo "Debes iniciar sesión para ver tus reservas.";
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

// ✅ Cancelar si viene ?cancelar=id
if (isset($_GET['cancelar'])) {
    $id_cancelar = intval($_GET['cancelar']);
    $stmt = $conn->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_cancelar, $usuario_id);
    $stmt->execute();
    header("Location: reservas.php");
    exit;
}

// ✅ Consulta con ID incluido
$stmt = $conn->prepare("SELECT id, fecha, hora, descripcion, estado FROM reservas WHERE id_usuario = ? AND estado = 'pendiente'");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div>
    <?php if ($resultado->num_rows > 0): ?>
        <?php while ($reserva = $resultado->fetch_assoc()): ?>
            <div style="margin-bottom: 12px; background: #f4f4f4; padding: 10px; border-radius: 8px;">
                <strong>🗓️ Fecha:</strong> <?php echo htmlspecialchars($reserva['fecha']); ?><br>
                <strong>🕒 Hora:</strong> <?php echo date("g:i A", strtotime($reserva['hora'])); ?><br>
                <strong>📌 Detalle:</strong> <?php echo htmlspecialchars($reserva['descripcion']); ?><br>
                <strong>⏳ Estado:</strong> <span style="color: #e67e22;"><?php echo htmlspecialchars($reserva['estado']); ?></span><br><br>
                <a href="reservas.php?cancelar=<?php echo $reserva['id']; ?>" onclick="return confirm('¿Estás seguro de cancelar esta reserva?')" style="color: red; font-weight: bold;">❌ Cancelar</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No tienes reservas pendientes.</p>
    <?php endif; ?>
</div>
