<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['usuario'];

$stmt = $conn->prepare("
    SELECT t.nombre AS nombre_torneo, t.fecha_inicio, tu.estado
    FROM torneos_usuarios tu
    JOIN torneos t ON tu.id_torneo = t.id
    WHERE tu.id_usuario = ?
");
$stmt->bind_param("i", $usuario['id']);
$stmt->execute();
$torneos = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Usuario</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
        }

        .perfil {
            background-color: rgb(40, 44, 52);
            color: white;
            padding: 30px 40px;
            width: 100%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            text-align: center;
            position: relative;
        }

        .perfil .logo-superior {
            position: absolute;
            top: 20px;
            left: 30px;
        }

        .perfil .logo-superior img {
            width: 80px;
            height: auto;
            border-radius: 8px;
        }

        .perfil img {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 60%;
            margin-bottom: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .perfil p {
            margin: 8px 0;
            font-size: 18px;
        }

        .perfil .saludo {
            font-size: 25px;
            margin-bottom: 12px;
        }

        .perfil a {
            display: inline-block;
            margin: 10px 10px 0;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
        }

        .perfil a.editar {
            background-color: #007bff;
        }

        .perfil a.logout {
            background-color: #dc3545;
        }

        .perfil a:hover {
            opacity: 0.9;
        }

        .main-content {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .box {
            background: white;
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .box h2 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        .torneo-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .torneo-item span {
            display: block;
            font-size: 15px;
            margin-top: 5px;
        }

        iframe {
            width: 100%;
            height: 500px;
            border: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<!-- Menú ancho con logo arriba a la izquierda -->
<div class="perfil">
    <div class="logo-superior">
        <img src="/img/isologo.png" alt="Logo">
    </div>

    <?php if (!empty($usuario['foto'])): ?>
        <img src="<?php echo $usuario['foto']; ?>" alt="Foto de perfil">
    <?php endif; ?>
    <p class="saludo">👋 Hola, <?php echo htmlspecialchars($usuario['nombre']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
    <a href="editar.php" class="editar">Editar Datos</a>
    <a href="logout.php" class="logout">Cerrar Sesión</a>
</div>

<div class="main-content">



    <!-- Reservas pendientes -->
    <div class="box">
        <h2>⏰ Reservas Pendientes</h2>
        <iframe src="reservas.php" style="height: 300px;"></iframe>
    </div>

    <!-- Partidos programados -->
    <div class="box">
        <h2>🏟️ Partidos Programados</h2>
        <iframe src="partidos.php" style="height: 400px;"></iframe>
    </div>

    <!-- Torneos inscritos -->
    <div class="box">
        <h2>📋 Torneos Inscritos</h2>
        <?php if ($torneos->num_rows > 0): ?>
            <?php while ($row = $torneos->fetch_assoc()): ?>
                <div class="torneo-item">
                    <strong><?php echo htmlspecialchars($row['nombre_torneo']); ?></strong>
                    <span>📅 <?php echo htmlspecialchars($row['fecha_inicio']); ?></span>
                    <span>Estado: <strong style="color: #007bff;"><?php echo htmlspecialchars($row['estado']); ?></strong></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center;">No estás inscrito en ningún torneo aún.</p>
        <?php endif; ?>
    </div>

    <!-- Calendario -->
    <div class="box">
        <h2>📅 Calendario de Reservas</h2>
        <iframe src="calendario.php"></iframe>
    </div>

</div>

</body>
</html>
