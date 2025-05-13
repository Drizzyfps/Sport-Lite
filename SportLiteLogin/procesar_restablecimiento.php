<?php
// Incluir archivo de conexión a la base de datos
require 'conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer-master/src/Exception.php';
require 'vendor/PHPMailer-master/src/PHPMailer.php';
require 'vendor/PHPMailer-master/src/SMTP.php';

// Función para generar un token seguro
function generarToken($longitud = 32) {
    return bin2hex(random_bytes($longitud));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email_restablecer'])) {
        $email = filter_var($_POST['email_restablecer'], FILTER_SANITIZE_EMAIL);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                // 1. Verificar si el correo electrónico existe en la base de datos
                $stmt = $conn->prepare("SELECT id, email FROM usuarios WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    // 2. Generar un token único de restablecimiento de contraseña
                    $token = generarToken();

                    // 3. Calcular la fecha de expiración del token (por ejemplo, 1 hora)
                    $fecha_expiracion = date("Y-m-d H:i:s", time() + 3600);

                    // 4. Guardar el token y la fecha de expiración en la base de datos
                    $stmt = $conn->prepare("UPDATE usuarios SET token_restablecimiento = :token, fecha_expiracion_token = :fecha_expiracion WHERE id = :id");
                    $stmt->bindParam(':token', $token);
                    $stmt->bindParam(':fecha_expiracion', $fecha_expiracion);
                    $stmt->bindParam(':id', $usuario['id']);
                    $stmt->execute();

                    // 5. Construir la URL para restablecer la contraseña
                    $url_restablecimiento = 'http://localhost/SportLiteLogin/nueva_contrasena.php?token=' . $token; // ¡REEMPLAZA CON TU URL REAL SI ES DIFERENTE!

                    // 6. Enviar un correo electrónico al usuario con la URL de restablecimiento
                    $asunto = 'Restablecer tu contraseña en Sport Lite';
                    $mensaje = '<!DOCTYPE html>';
                    $mensaje .= '<html lang="es">';
                    $mensaje .= '<head>';
                    $mensaje .= '<meta charset="UTF-8">';
                    $mensaje .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                    $mensaje .= '<title>Restablecer tu contraseña en Sport Lite</title>';
                    $mensaje .= '</head>';
                    $mensaje .= '<body style="font-family: sans-serif; line-height: 1.6;">';
                    $mensaje .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc;">';
                    $mensaje .= '<h2 style="color: #007bff;">Restablecer tu contraseña en Sport Lite</h2>';
                    $mensaje .= '<p>Hola,</p>';
                    $mensaje .= '<p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para hacerlo:</p>';
                    $mensaje .= '<p style="margin-bottom: 20px;"><a href="' . $url_restablecimiento . '" style="display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Restablecer mi contraseña</a></p>';
                    $mensaje .= '<p>Si el botón de arriba no funciona, puedes copiar y pegar el siguiente enlace en tu navegador:</p>';
                    $mensaje .= '<p><a href="' . $url_restablecimiento . '">' . $url_restablecimiento . '</a></p>';
                    $mensaje .= '<p style="margin-top: 20px;">Este enlace es válido por 1 hora.</p>';
                    $mensaje .= '<p>Si no solicitaste este restablecimiento, puedes ignorar este correo.</p>';
                    $mensaje .= '<p>Saludos,<br>El equipo de Sport Lite</p>';
                    $mensaje .= '</div>';
                    $mensaje .= '</body>';
                    $mensaje .= '</html>';

                    // *** CONFIGURACIÓN DE PHPMailer ***
                    $mail = new PHPMailer(true);

                    try {
                        // Configuración del servidor SMTP de Gmail
                        $mail->SMTPDebug = SMTP::DEBUG_OFF;
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'sportliteoficial@gmail.com';
                        $mail->Password   = 'kwba gefj emne lcqv';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        // Remitentes y destinatarios
                        $mail->setFrom('sportliteoficial@gmail.com', 'Sport Lite');
                        $mail->addAddress($usuario['email'], $usuario['email']);

                        // Contenido del correo electrónico
                        $mail->isHTML(true); // ¡CAMBIADO A TRUE PARA ENVIAR HTML!
                        $mail->Subject = $asunto;
                        $mail->Body    = $mensaje;

                        $mail->send();
                        header('Location: restablecimiento_enviado.php');
                        exit();

                    } catch (Exception $e) {
                        header('Location: restablecer_contrasena.php?error=Error al enviar el correo electrónico: ' . $mail->ErrorInfo);
                        exit();
                    }
                    // *** FIN DE CONFIGURACIÓN DE PHPMailer ***

                } else {
                    // El correo electrónico no existe en la base de datos
                    header('Location: restablecer_contrasena.php?error=No se encontró ninguna cuenta con ese correo electrónico.');
                    exit();
                }

            } catch (PDOException $e) {
                // Error de base de datos
                header('Location: restablecer_contrasena.php?error=Error de base de datos: ' . $e->getMessage());
                exit();
            }

        } else {
            // El correo electrónico no tiene un formato válido
            header('Location: restablecer_contrasena.php?error=El correo electrónico ingresado no es válido.');
            exit();
        }
    } else {
        // No se recibió el correo electrónico por POST
        header('Location: restablecer_contrasena.php?error=Hubo un problema con la solicitud. Intenta de nuevo.');
        exit();
    }
} else {
    // Si se intenta acceder al script por GET
    header('Location: restablecer_contrasena.php');
    exit();
}
?>