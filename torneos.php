<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    exit("Acceso no autorizado.");
}

$usuario_id = $_SESSION['usuario']['id'];

// ✅ Cancelar inscripción
if (isset($_GET['cancelar'])) {
    $id_cancelar = intval($_GET['cancelar']);
    $stmt = $conn->prepare("DELETE FROM torneos_usuarios WHERE id_usuario = ? AND id_torneo = ?");
    $stmt->bind_param("ii", $usuario_id, $id_cancelar);
    $stmt->execute();
    header("Location: torneos.php");
    exit;
}

// ✅ Consultar torneos inscritos
$stmt = $conn->prepare("
    SELECT t.id, t.nombre, t.fecha_inicio, tu.estado
    FROM torneos_usuarios tu
    JOIN torneos t ON tu.id_torneo = t.id
    WHERE tu.id_usuario = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Torneos Inscritos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f9f9f9;
        }

        .titulo-fijo {
            position: sticky;
            top: 0;
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            z-index: 10;
        }

        .contenedor {
            padding: 20px;
        }

        .torneo-item {
            background: #ffffff;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            line-height: 1.6;
        }

        .torneo-item span {
            display: block;
            font-size: 15px;
            margin: 4px 0;
        }

        .torneo-item a {
            color: red;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .torneo-item a:hover {
            text-decoration: underline;
        }

        .estado {
            color: #e67e22;
            font-weight: bold;
        }

        p {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="titulo-fijo">📋 Torneos Inscritos</div>

    <div class="contenedor">
        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($torneo = $resultado->fetch_assoc()): ?>
                <div class="torneo-item">
                    <span>🏆 <strong>Torneo:</strong> <?php echo htmlspecialchars($torneo['nombre']); ?></span>
                    <span>🗓️ <strong>Fecha:</strong> <?php echo date("Y-m-d", strtotime($torneo['fecha_inicio'])); ?></span>
                    <span>🕒 <strong>Hora:</strong> <?php echo date("g:i A", strtotime($torneo['fecha_inicio'])); ?></span>
                    <span>⏳ <strong>Estado:</strong> <span class="estado"><?php echo htmlspecialchars($torneo['estado']); ?></span></span>
                    <a href="torneos.php?cancelar=<?php echo $torneo['id']; ?>" onclick="return confirm('¿Estás seguro de cancelar tu inscripción en este torneo?')">❌ Cancelar inscripción</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No estás inscrito en ningún torneo aún.</p>
        <?php endif; ?>
    </div>

</body>
</html>
