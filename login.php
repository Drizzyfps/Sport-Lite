<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $clave = $_POST['clave'];

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($clave, $row['clave'])) {
            $_SESSION['usuario'] = $row;
            header("Location: panel.php");
            exit;
        } else {
            $error = "⚠️ Clave incorrecta.";
        }
    } else {
        $error = "⚠️ Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            width: 400px;
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
        }

        .login-box input {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .login-box button {
            width: 100%;
            padding: 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .login-box button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            text-align: center;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" autocomplete="on">
            <input type="email" name="email" placeholder="Correo electrónico" autocomplete="email" required>
            <input type="password" name="clave" placeholder="Contraseña" autocomplete="current-password" required>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>
