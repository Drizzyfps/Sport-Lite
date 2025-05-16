<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    echo "Debes iniciar sesión.";
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

// ✅ Consultamos fecha, hora y descripción de reservas pendientes
$stmt = $conn->prepare("SELECT fecha, hora, descripcion FROM reservas WHERE id_usuario = ? AND estado = 'pendiente'");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Creamos un arreglo con la fecha como clave y lista de hora + descripción
$reservas = [];
while ($row = $result->fetch_assoc()) {
    $fecha = $row['fecha'];
    $hora_formateada = date("g:i A", strtotime($row['hora'])); // 12 horas con AM/PM

    if (!isset($reservas[$fecha])) {
        $reservas[$fecha] = [];
    }

    $reservas[$fecha][] = $hora_formateada . " - " . $row['descripcion'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario de Reservas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: rgb(60, 70, 90);
        }

        .contenedor {
            border-radius: 16px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        #calendario {
            display: block;
            margin: 0 auto;
            font-size: 20px;
            padding: 10px;
            border-radius: 8px;
            border: none;
            text-align: center;
        }

        .flatpickr-calendar {
            transform: scale(1.2);
            transform-origin: top;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <input type="text" id="calendario">
    </div>

    <script>
        const reservas = <?php echo json_encode($reservas); ?>;

        flatpickr("#calendario", {
            inline: true,
            locale: "es",
            defaultDate: "today",
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const fecha = dayElem.dateObj.toISOString().split('T')[0];
                if (reservas[fecha]) {
                    dayElem.style.backgroundColor = '#28a745';
                    dayElem.style.color = 'white';
                    dayElem.title = reservas[fecha].join("\n");
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (reservas[dateStr]) {
                    let mensaje = "📌 Reservas para el " + dateStr + ":\n\n";
                    reservas[dateStr].forEach((desc, i) => {
                        mensaje += `${i + 1}. ${desc}\n`;
                    });
                    alert(mensaje);
                }
            }
        });
    </script>
</body>
</html>
