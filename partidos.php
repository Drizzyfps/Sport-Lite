<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

// Obtener torneos en los que está inscrito
$torneos_usuario = [];
$torneos_disponibles = $conn->query("SELECT t.id, t.nombre FROM torneos t 
    JOIN torneos_usuarios tu ON t.id = tu.id_torneo 
    WHERE tu.id_usuario = $usuario_id");

while ($row = $torneos_disponibles->fetch_assoc()) {
    $torneos_usuario[$row['id']] = $row['nombre'];
}

// Filtro
$filtro_id = isset($_GET['torneo_id']) ? (int)$_GET['torneo_id'] : 0;

$partidos = [];
if (!empty($torneos_usuario)) {
    $ids = implode(',', array_keys($torneos_usuario));
    $query = "SELECT * FROM partidos WHERE id_torneo IN ($ids)";
    if ($filtro_id > 0) {
        $query .= " AND id_torneo = $filtro_id";
    }
    $query .= " ORDER BY fecha, hora";
    $partidos = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Partidos Programados</title>
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

        form {
            text-align: center;
            margin-bottom: 25px;
        }

        select {
            padding: 8px;
            font-size: 15px;
            border-radius: 6px;
        }

        .partido-item {
            background: #ffffff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            line-height: 1.6;
        }

        .partido-item span {
            display: block;
            font-size: 15px;
            margin: 4px 0;
        }

        p {
            text-align: center;
        }
    </style>
</head>
<body>

    

    <div class="contenedor">
        <form method="get">
            <label for="torneo_id">Filtrar por Torneo:</label>
            <select name="torneo_id" id="torneo_id" onchange="this.form.submit()">
                <option value="0">-- Todos --</option>
                <?php foreach ($torneos_usuario as $id => $nombre): ?>
                    <option value="<?= $id ?>" <?= $filtro_id == $id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nombre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (!empty($partidos) && $partidos->num_rows > 0): ?>
            <?php while ($p = $partidos->fetch_assoc()): ?>
                <div class="partido-item">
                    <span>🏟️ <strong>Torneo:</strong> <?= htmlspecialchars($torneos_usuario[$p['id_torneo']] ?? ''); ?></span>
                    <span>🆚 <strong>Equipos:</strong> <?= htmlspecialchars($p['equipo1']) ?> vs <?= htmlspecialchars($p['equipo2']) ?></span>
                    <span>🗓️ <strong>Fecha:</strong> <?= date("Y-m-d", strtotime($p['fecha'])) ?></span>
                    <span>🕒 <strong>Hora:</strong> <?= date("g:i A", strtotime($p['hora'])) ?></span>
                    <span>📍 <strong>Lugar:</strong> <?= htmlspecialchars($p['lugar']) ?></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay partidos programados para este torneo.</p>
        <?php endif; ?>
    </div>

</body>
</html>
