<?php

require_once __DIR__ . '/includes/db_connection.php';

header('Content-Type: application/json');

$cancha_id = filter_input(INPUT_GET, 'cancha_id', FILTER_VALIDATE_INT);
$fecha_reserva_str = filter_input(INPUT_GET, 'fecha_reserva', FILTER_SANITIZE_STRING);

$available_times_data = [];
$errors = [];

// Validar parámetros
if ($cancha_id === false || empty($fecha_reserva_str)) {
    $errors[] = "Datos de cancha o fecha incompletos o inválidos.";
    echo json_encode(['data' => [], 'errors' => $errors]);
    exit();
}

// Validar formato de fecha para evitar errores de DateTime
if (!DateTime::createFromFormat('Y-m-d', $fecha_reserva_str)) {
    $errors[] = "Formato de fecha inválido.";
    echo json_encode(['data' => [], 'errors' => $errors]);
    exit();
}

$fecha_reserva_dt = new DateTime($fecha_reserva_str);
$current_datetime = new DateTime(); 

$business_segments = [
    ['start' => '07:00:00', 'end' => '11:00:00'], 
    ['start' => '13:00:00', 'end' => '23:00:00']  
];

$allowed_durations = [0.5, 1.0, 1.5, 2.0, 2.5, 3.0];

$conn = get_db_connection();

if (!$conn) {
    $errors[] = "No se pudo conectar a la base de datos.";
    echo json_encode(['data' => [], 'errors' => $errors]);
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT hora_inicio, hora_fin FROM reservas
        WHERE cancha_id = :cancha_id AND fecha_reserva = :fecha_reserva
        AND estado = 'Confirmada'
        ORDER BY hora_inicio ASC
    ");
    $stmt->bindParam(':cancha_id', $cancha_id, PDO::PARAM_INT);
    $stmt->bindParam(':fecha_reserva', $fecha_reserva_str, PDO::PARAM_STR);
    $stmt->execute();
    $existing_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($business_segments as $segment) {
        $segment_start_dt = new DateTime($fecha_reserva_str . ' ' . $segment['start']);
        $segment_end_dt = new DateTime($fecha_reserva_str . ' ' . $segment['end']);

        $interval = new DateInterval('PT30M');
        $period = new DatePeriod(new DateTimeImmutable($segment_start_dt->format('Y-m-d H:i:s')), $interval, $segment_end_dt);


        foreach ($period as $slot_start_dt) {

            if ($fecha_reserva_dt->format('Y-m-d') < $current_datetime->format('Y-m-d')) {
                continue; 
            }
            if ($fecha_reserva_dt->format('Y-m-d') === $current_datetime->format('Y-m-d') && $slot_start_dt < $current_datetime) {

                continue;
            }

            $slot_start_str = $slot_start_dt->format('H:i'); 

            $possible_durations_for_slot = [];

            foreach ($allowed_durations as $duration) {
                $slot_end_dt = clone $slot_start_dt;
                $slot_end_dt->modify("+" . ($duration * 60) . " minutes");

                if ($slot_end_dt > $segment_end_dt) {
                    continue; 
                }

                $is_available_for_duration = true;
                foreach ($existing_reservations as $res) {
                    $res_start_dt = new DateTime($fecha_reserva_str . ' ' . $res['hora_inicio']);
                    $res_end_dt = new DateTime($fecha_reserva_str . ' ' . $res['hora_fin']);

                    if (($slot_start_dt < $res_end_dt) && ($slot_end_dt > $res_start_dt)) {
                        $is_available_for_duration = false;
                        break;
                    }
                }

                if ($is_available_for_duration) {
                    $possible_durations_for_slot[] = $duration;
                }
            }

            if (!empty($possible_durations_for_slot)) {
                $available_times_data[] = [
                    'time' => $slot_start_str,
                    'durations' => $possible_durations_for_slot
                ];
            }
        }
    }

    $unique_times_data = [];
    foreach ($available_times_data as $slot) {
        if (!isset($unique_times_data[$slot['time']])) {
            $unique_times_data[$slot['time']] = $slot;
        } else {
            $unique_times_data[$slot['time']]['durations'] = array_unique(array_merge($unique_times_data[$slot['time']]['durations'], $slot['durations']));
            sort($unique_times_data[$slot['time']]['durations']); 
        }
    }

    usort($unique_times_data, function($a, $b) {
        return strtotime($a['time']) - strtotime($b['time']);
    });

    $available_times_data = array_values($unique_times_data); 

} catch (PDOException $e) {
    $errors[] = "Error de base de datos al obtener horarios: " . $e->getMessage();
} catch (Exception $e) { 
    $errors[] = "Error al procesar fechas/horas: " . $e->getMessage();
} finally {
    $conn = null;
}

echo json_encode(['data' => $available_times_data, 'errors' => $errors]);
?>