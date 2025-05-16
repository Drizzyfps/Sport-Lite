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
$id = $usuario['id'];

// Si se presiona "Quitar imagen"
if (isset($_POST['quitar_foto']) && !empty($usuario['foto'])) {
    if (file_exists($usuario['foto'])) {
        unlink($usuario['foto']); // elimina el archivo
    }
    $usuario['foto'] = ''; // limpia en memoria
    $stmt = $conn->prepare("UPDATE usuarios SET foto = '' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['usuario']['foto'] = '';
    header("Location: panel.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['quitar_foto'])) {
    $nuevoNombre = $_POST['nombre'];
    $nuevoEmail = $_POST['email'];
    $nuevaFoto = $usuario['foto']; // valor actual por defecto

    // DEPURACIÓN: Mostrar información del archivo subido
    echo "<pre>";
    print_r($_FILES['foto']);
    echo "</pre>";

    // Si se sube nueva imagen
    if (!empty($_FILES['foto']['name'])) {
        $nombreArchivo = uniqid() . "_" . basename($_FILES['foto']['name']);
        $rutaDestino = "img/usuarios/" . $nombreArchivo;
        $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));

        if (in_array($tipoArchivo, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                $nuevaFoto = $rutaDestino;
            } else {
                $error = "❌ Error al mover la imagen a '$rutaDestino'";
            }
        } else {
            $error = "❌ Tipo de imagen no permitido: $tipoArchivo";
        }
    }

    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, foto = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nuevoNombre, $nuevoEmail, $nuevaFoto, $id);

    if ($stmt->execute()) {
        $_SESSION['usuario']['nombre'] = $nuevoNombre;
        $_SESSION['usuario']['email'] = $nuevoEmail;
        $_SESSION['usuario']['foto'] = $nuevaFoto;
        header("Location: panel.php");
        exit;
    } else {
        $error = "Error al actualizar: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }

        .edit-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            width: 400px;
        }

        .edit-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .edit-box input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .edit-box button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .edit-box button:hover {
            background-color: #218838;
        }

        .edit-box img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            margin: 10px auto;
        }

        .error {
            color: red;
            text-align: center;
            font-size: 14px;
        }

        .quitar-btn {
            background-color: #dc3545;
            margin-top: 10px;
        }

        .quitar-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="edit-box">
        <h2>Editar Perfil</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['usuario']['email']); ?>" required>

            <?php if (!empty($_SESSION['usuario']['foto'])): ?>
                <img src="<?php echo $_SESSION['usuario']['foto']; ?>" alt="Foto actual">
            <?php endif; ?>

            <input type="file" name="foto" accept="image/*">

            <button type="submit">Guardar Cambios</button>
        </form>

        <?php if (!empty($_SESSION['usuario']['foto'])): ?>
            <form method="post">
                <input type="hidden" name="quitar_foto" value="1">
                <button type="submit" class="quitar-btn">Quitar imagen</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
