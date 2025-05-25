<?php
// Sección: Configuración y conexión a la base de datos
require_once __DIR__ . '/includes/db_connection.php';

// Sección: Inicialización de variables para el estado del formulario y mensajes
$mensaje_resultado = "";
$tipo_mensaje = "";

$nombre_val = '';
$apellido_val = '';
$cancha_id_val = '';
$fecha_reserva_val = '';
$hora_inicio_val = '';
$hora_fin_val = '';
$celular_val = '';
$costo_total_display = "---";

// Variables para almacenar las opciones de hora
$horas_inicio_options = '<option value="">Selecciona cancha y fecha</option>';
$horas_fin_options = '<option value="">Selecciona hora de inicio</option>';


// Sección: Definición de horarios de operación del negocio
$business_segments = [
    ['start' => '07:00:00', 'end' => '11:00:00'], // 7 AM a 11 AM
    ['start' => '13:00:00', 'end' => '23:00:00']  // 1 PM a 11 PM
];

// Sección: Función auxiliar para formatear la duración para mostrar en el mensaje de éxito
function format_duration_display($duration_hours) {
    if ($duration_hours === 0.5) {
        return '30 minutos';
    } else if ($duration_hours % 1 === 0) {
        return $duration_hours . ' hora' . ($duration_hours > 1 ? 's' : '');
    } else {
        $horas_enteras = floor($duration_hours);
        $minutos = ($duration_hours - $horas_enteras) * 60;
        return $horas_enteras . ' hora' . ($horas_enteras > 1 ? 's' : '') . ' y ' . $minutos . ' minutos';
    }
}

// Sección: Lógica de procesamiento de la reserva cuando el formulario se envía (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitizar y validar los datos del formulario
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $apellido = htmlspecialchars(trim($_POST['apellido'] ?? ''));
    $cancha_id = filter_var($_POST['cancha_id'] ?? '', FILTER_VALIDATE_INT);
    $fecha_reserva = htmlspecialchars(trim($_POST['fecha_reserva'] ?? ''));
    $hora_inicio = htmlspecialchars(trim($_POST['hora_inicio'] ?? ''));
    $hora_fin = htmlspecialchars(trim($_POST['hora_fin'] ?? ''));
    $celular = htmlspecialchars(trim($_POST['celular'] ?? ''));

    // Asignar los valores a las variables _val para que se mantengan si hay un error
    $nombre_val = $nombre;
    $apellido_val = $apellido;
    $cancha_id_val = $_POST['cancha_id'] ?? '';
    $fecha_reserva_val = $fecha_reserva;
    $hora_inicio_val = $hora_inicio;
    $hora_fin_val = $hora_fin;
    $celular_val = $celular;

    // Validación de campos obligatorios
    if (empty($nombre) || empty($apellido) || $cancha_id === false || empty($fecha_reserva) || empty($hora_inicio) || empty($hora_fin) || empty($celular)) {
        $mensaje_resultado = "Por favor, completa todos los campos obligatorios.";
        $tipo_mensaje = "error-message";
    } else {
        // Validación de formato del celular
        if (!preg_match('/^[0-9]{7,10}$/', $celular)) {
             $mensaje_resultado = "El número de celular no es válido. Debe contener entre 7 y 10 dígitos numéricos.";
             $tipo_mensaje = "error-message";
        } else {
            $conn = get_db_connection();
            if ($conn) {
                try {
                    $conn->beginTransaction();

                    $inicio_dt = new DateTime($fecha_reserva . ' ' . $hora_inicio);
                    $fin_dt = new DateTime($fecha_reserva . ' ' . $hora_fin);

                    // Cálculo de duración en horas
                    $interval = $inicio_dt->diff($fin_dt);
                    $duracion_minutos = ($interval->days * 24 * 60) + ($interval->h * 60) + ($interval->i);
                    $duracion_horas = $duracion_minutos / 60.0;

                    // Sección: Obtener precios de la cancha para calcular el costo
                    $stmt_precios = $conn->prepare("SELECT precio_hora, precio_media_hora FROM canchas WHERE id = :cancha_id");
                    $stmt_precios->bindParam(':cancha_id', $cancha_id, PDO::PARAM_INT);
                    $stmt_precios->execute();
                    $precios_cancha = $stmt_precios->fetch(PDO::FETCH_ASSOC);

                    $costo_total = 0;
                    if ($precios_cancha) {
                        $precio_hora = (float)$precios_cancha['precio_hora'];
                        $precio_media_hora = (float)$precios_cancha['precio_media_hora'];

                        if ($duracion_horas > 0) {
                            $horas_completas = floor($duracion_horas);
                            $medias_horas_restantes = ($duracion_horas - $horas_completas) * 2;

                            $costo_total = ($horas_completas * $precio_hora) + ($medias_horas_restantes * $precio_media_hora);
                        }
                    } else {
                        throw new Exception("No se pudieron obtener los precios para la cancha seleccionada.");
                    }

                    // Sección: Validaciones de horas y duraciones en el backend
                    $is_time_valid_business_hours = false;
                    foreach ($business_segments as $segment) {
                        $segment_start_dt = new DateTime($fecha_reserva . ' ' . $segment['start']);
                        $segment_end_dt = new DateTime($fecha_reserva . ' ' . $segment['end']);

                        // Asegurar que el inicio y el fin de la reserva estén dentro de un único segmento de negocio
                        if ($inicio_dt >= $segment_start_dt && $fin_dt <= $segment_end_dt && $inicio_dt < $fin_dt) {
                            $is_time_valid_business_hours = true;
                            break;
                        }
                    }

                    $now = new DateTime();
                    $fecha_reserva_dt_check = new DateTime($fecha_reserva);

                    // Si la fecha de reserva ya pasó (ej. ayer) o si es hoy y la hora de inicio ya pasó
                    if ($fecha_reserva_dt_check->format('Y-m-d') < $now->format('Y-m-d')) {
                        $is_time_valid_business_hours = false;
                    } elseif ($fecha_reserva_dt_check->format('Y-m-d') === $now->format('Y-m-d') && $inicio_dt < $now) {
                        $is_time_valid_business_hours = false;
                    }

                    // Asegurar que la duración sea en múltiplos de 0.5 (30 minutos) y mayor que cero
                    if (fmod($duracion_horas, 0.5) !== 0.0 || $duracion_horas <= 0) {
                        $is_time_valid_business_hours = false;
                    }

                    if (!$is_time_valid_business_hours) {
                        $mensaje_resultado = "La hora de inicio o la hora de fin seleccionada no son válidas según los horarios de operación, la duración (mínimo 30 min) o ya pasaron. Asegúrate de seleccionar horarios futuros y válidos.";
                        $tipo_mensaje = "error-message";
                        $conn->rollBack();
                    } else {
                        // Sección: Verificar disponibilidad (solapamiento) para la CANCHA ESPECÍFICA
                        $stmt_check = $conn->prepare("
                            SELECT COUNT(*) AS count_reservas FROM reservas
                            WHERE cancha_id = :cancha_id
                            AND fecha_reserva = :fecha_reserva
                            AND (
                                (hora_inicio < :hora_fin AND hora_fin > :hora_inicio)
                            )
                            AND estado = 'Confirmada'
                        ");
                        $stmt_check->bindParam(':cancha_id', $cancha_id, PDO::PARAM_INT);
                        $stmt_check->bindParam(':fecha_reserva', $fecha_reserva, PDO::PARAM_STR);
                        $stmt_check->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
                        $stmt_check->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
                        $stmt_check->execute();
                        $reservas_existentes = $stmt_check->fetchColumn();

                        if ($reservas_existentes > 0) {
                            $stmt_cancha_nombre = $conn->prepare("SELECT nombre FROM canchas WHERE id = :cancha_id");
                            $stmt_cancha_nombre->bindParam(':cancha_id', $cancha_id, PDO::PARAM_INT);
                            $stmt_cancha_nombre->execute();
                            $cancha_nombre_display = $stmt_cancha_nombre->fetchColumn() ?: 'la cancha seleccionada';

                            $mensaje_resultado = "¡Lo sentimos! La cancha " . htmlspecialchars($cancha_nombre_display) . " ya está reservada para el " . htmlspecialchars($fecha_reserva) . " entre " . htmlspecialchars(substr($hora_inicio, 0, 5)) . " y " . htmlspecialchars(substr($hora_fin, 0, 5)) . ". Por favor, elige otro horario.";
                            $tipo_mensaje = "error-message";
                            $conn->rollBack();
                        } else {
                            // Sección: Si está disponible, insertar la nueva reserva
                            $stmt_insert = $conn->prepare("
                                INSERT INTO reservas (cancha_id, usuario_nombre, usuario_apellido, usuario_celular, fecha_reserva, hora_inicio, hora_fin, duracion_horas, costo_total, estado)
                                VALUES (:cancha_id, :nombre, :apellido, :celular, :fecha_reserva, :hora_inicio, :hora_fin, :duracion_horas, :costo_total, 'Confirmada')
                            ");
                            $stmt_insert->bindParam(':cancha_id', $cancha_id, PDO::PARAM_INT);
                            $stmt_insert->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':apellido', $apellido, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':celular', $celular, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':fecha_reserva', $fecha_reserva, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':duracion_horas', $duracion_horas, PDO::PARAM_STR);
                            $stmt_insert->bindParam(':costo_total', $costo_total, PDO::PARAM_STR);

                            if ($stmt_insert->execute()) {
                                $stmt_cancha_nombre = $conn->prepare("SELECT nombre FROM canchas WHERE id = :cancha_id");
                                $stmt_cancha_nombre->bindParam(':cancha_id', $cancha_id, PDO::PARAM_INT);
                                $stmt_cancha_nombre->execute();
                                $cancha_nombre_display = $stmt_cancha_nombre->fetchColumn() ?: 'la cancha';

                                $final_message = "¡Éxito, " . htmlspecialchars($nombre) . "! Tu reserva para la " . htmlspecialchars($cancha_nombre_display) . " el " . htmlspecialchars($fecha_reserva) . " de " . htmlspecialchars(substr($hora_inicio, 0, 5)) . " a " . htmlspecialchars(substr($hora_fin, 0, 5)) . " (duración: " . format_duration_display($duracion_horas) . ") ha sido confirmada. El costo total es: $" . number_format($costo_total, 0, ',', '.') . " COP.";
                                
                                $conn->commit();

                                // Redirigir para limpiar el formulario y mostrar el mensaje
                                header("Location: reservar_cancha.php?msg_type=success-message&msg=" . urlencode($final_message));
                                exit();

                            } else {
                                $mensaje_resultado = "Hubo un error al procesar tu reserva. Por favor, inténtalo de nuevo.";
                                $tipo_mensaje = "error-message";
                                $conn->rollBack();
                            }
                        }
                    }
                } catch (PDOException $e) {
                    if ($conn && $conn->inTransaction()) {
                        $conn->rollBack();
                    }
                    $mensaje_resultado = "Error de base de datos durante la reserva: " . $e->getMessage() . " (Código: DB-RES)";
                    $tipo_mensaje = "error-message";
                } catch (Exception $e) {
                    $mensaje_resultado = "Error interno del servidor al procesar la reserva: " . $e->getMessage();
                    $tipo_mensaje = "error-message";
                    if ($conn && $conn->inTransaction()) {
                        $conn->rollBack();
                    }
                } finally {
                    $conn = null;
                }
            } else {
                $mensaje_resultado = "No se pudo establecer conexión con la base de datos para procesar la reserva. (Código: DB-PROC)";
                $tipo_mensaje = "error-message";
            }
        }
    }
} else {
    // Sección: Manejo de la carga inicial de la página o redirección GET
    if (isset($_GET['msg_type']) && isset($_GET['msg'])) {
        $tipo_mensaje = htmlspecialchars($_GET['msg_type']);
        $mensaje_resultado = htmlspecialchars($_GET['msg']);
    }
}


// Sección: Lógica para cargar las canchas disponibles al SELECT del formulario
$canchas = [];
$error_carga_canchas = '';
$conn = get_db_connection();

if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT id, nombre, precio_hora, precio_media_hora FROM canchas ORDER BY nombre ASC");
        $stmt->execute();
        $canchas = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_carga_canchas = "Error al cargar las canchas disponibles: " . $e->getMessage();
    } finally {
        $conn = null;
    }
} else {
    $error_carga_canchas = "No se pudo establecer conexión con la base de datos para cargar las canchas.";
}

// Sección: Obtener reservas existentes para la cancha y fecha seleccionada (para que JS las use)
$existing_reservations_json = '[]';
$conn_for_reservations = get_db_connection();
if ($conn_for_reservations) {
    try {
        $date_to_fetch_reservations = !empty($fecha_reserva_val) ? $fecha_reserva_val : date('Y-m-d');
        $cancha_to_fetch_reservations = !empty($cancha_id_val) ? $cancha_id_val : null;

        if ($cancha_to_fetch_reservations) {
            $stmt = $conn_for_reservations->prepare("
                SELECT hora_inicio, hora_fin FROM reservas
                WHERE cancha_id = :cancha_id AND fecha_reserva = :fecha_reserva
                AND estado = 'Confirmada'
                ORDER BY hora_inicio ASC
            ");
            $stmt->bindParam(':cancha_id', $cancha_to_fetch_reservations, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_reserva', $date_to_fetch_reservations, PDO::PARAM_STR);
            $stmt->execute();
            $existing_reservations_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $existing_reservations_json = json_encode($existing_reservations_data);
        }
    } catch (PDOException $e) {
        error_log("Error al cargar reservas para JS: " . $e->getMessage());
    } finally {
        $conn_for_reservations = null;
    }
}


// Sección: Codificar datos para JavaScript
$canchas_json = json_encode($canchas);
$business_segments_json = json_encode($business_segments);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Cancha - SportLite</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="background-radial-gradient"></div>
    <div class="container glassmorphism">
        <h1 class="form-title">¡Reserva tu Cancha!</h1>
        <p class="form-subtitle">Selecciona los detalles de tu juego y asegura tu espacio.</p>

        <?php
        if ($mensaje_resultado) {
            echo "<div class='message " . htmlspecialchars($tipo_mensaje) . "'>" . htmlspecialchars($mensaje_resultado) . "</div>";
        }
        if ($error_carga_canchas) {
            echo "<div class='message error-message'><p>" . htmlspecialchars($error_carga_canchas) . "</p></div>";
        }
        ?>

        <form action="reservar_cancha.php" method="POST" class="reservation-form">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required value="<?php echo htmlspecialchars($nombre_val); ?>">
            </div>

            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" placeholder="Tu apellido" required value="<?php echo htmlspecialchars($apellido_val); ?>">
            </div>

            <div class="form-group">
                <label for="cancha_id">Cancha:</label>
                <select id="cancha_id" name="cancha_id" required>
                    <option value="">Selecciona una cancha</option>
                    <?php foreach ($canchas as $cancha): ?>
                        <option value="<?php echo htmlspecialchars($cancha['id']); ?>"
                            <?php echo ($cancha_id_val == $cancha['id']) ? 'selected' : ''; ?>
                            data-precio-hora="<?php echo htmlspecialchars($cancha['precio_hora']); ?>"
                            data-precio-media-hora="<?php echo htmlspecialchars($cancha['precio_media_hora']); ?>">
                            <?php echo htmlspecialchars($cancha['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha_reserva">Fecha:</label>
                <input type="date" id="fecha_reserva" name="fecha_reserva" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fecha_reserva_val); ?>">
            </div>

            <div class="form-group">
                <label for="hora_inicio">Hora Inicio:</label>
                <select id="hora_inicio" name="hora_inicio" required>
                    <?php echo $horas_inicio_options; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="hora_fin">Hora Fin:</label>
                <select id="hora_fin" name="hora_fin" required disabled>
                    <?php echo $horas_fin_options; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="celular">Celular:</label>
                <input type="tel" id="celular" name="celular" placeholder="Ej: 3001234567" required value="<?php echo htmlspecialchars($celular_val); ?>">
            </div>

            <div class="form-group total-cost-display">
                <label>Total a Pagar:</label>
                <span id="costo_total_display"><?php echo htmlspecialchars($costo_total_display); ?></span>
            </div>

            <button type="submit" class="btn btn-primary">CONFIRMAR RESERVA</button>
        </form>
        
        <div class="back-button-wrapper">
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Volver / Cancelar</button>
        </div>
    </div>

    <script>
        // Sección: Pasar datos de PHP a JavaScript
        const PHP_CANCHAS = <?php echo $canchas_json; ?>;
        const PHP_BUSINESS_SEGMENTS = <?php echo $business_segments_json; ?>;
        const PHP_EXISTING_RESERVATIONS = <?php echo $existing_reservations_json; ?>;
        const PHP_SELECTED_CANCHA_ID = "<?php echo htmlspecialchars($cancha_id_val); ?>";
        const PHP_SELECTED_DATE = "<?php echo htmlspecialchars($fecha_reserva_val); ?>";
        const PHP_SELECTED_HOUR_START = "<?php echo htmlspecialchars($hora_inicio_val); ?>";
        const PHP_SELECTED_HOUR_END = "<?php echo htmlspecialchars($hora_fin_val); ?>";
    </script>
    <script src="js/scripts.js"></script>
</body>
</html>