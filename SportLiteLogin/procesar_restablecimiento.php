<?php
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
                // Verificar si el correo electrónico existe en la base de datos y obtener el nombre
                $stmt = $conn->prepare("SELECT id, email, nombre FROM usuarios WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    // Generar un token único de restablecimiento de contraseña
                    $token = generarToken();

                    // Calcular la fecha de expiración del token (por ejemplo, 1 hora)
                    $fecha_expiracion = date("Y-m-d H:i:s", time() + 3600);

                    // Guardar el token y la fecha de expiración en la base de datos
                    $stmt = $conn->prepare("UPDATE usuarios SET token_restablecimiento = :token, fecha_expiracion_token = :fecha_expiracion WHERE id = :id");
                    $stmt->bindParam(':token', $token);
                    $stmt->bindParam(':fecha_expiracion', $fecha_expiracion);
                    $stmt->bindParam(':id', $usuario['id']);
                    $stmt->execute();

                    // Construir la URL para restablecer la contraseña
                    $url_restablecimiento = 'http://localhost/SportLiteLogin/nueva_contrasena.php?token=' . $token; // ¡REEMPLAZA CON TU URL REAL SI ES DIFERENTE!

                    // Enviar un correo electrónico al usuario con la URL de restablecimiento
                    $asunto = 'Restablecer tu contraseña en Sport Lite';
                    $mensaje = '<!DOCTYPE html>';
                    $mensaje .= '<html lang="es">';
                    $mensaje .= '<head>';
                    $mensaje .= '<meta charset="UTF-8">';
                    $mensaje .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                    $mensaje .= '<title>Restablecer tu contraseña en Sport Lite</title>';
                    $mensaje .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
                    $mensaje .= '</head>';
                    $mensaje .= '<body style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; color: #444; line-height: 1.6;">';
                    $mensaje .= '<div style="max-width: 600px; margin: 30px auto; padding: 40px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #f8f8f8;">';
                    $mensaje .= '<h2 style="color: #337ab7; margin-top: 0; margin-bottom: 30px; text-align: center;">Restablecer tu contraseña en Sport Lite</h2>';
                    $mensaje .= '<p style="margin-bottom: 20px;">Estimado/a ' . htmlspecialchars($usuario['nombre']) . ',</p>';
                    $mensaje .= '<p style="margin-bottom: 20px;">Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. Para continuar, haz clic en el siguiente botón:</p>';
                    $mensaje .= '<p style="text-align: center; margin-bottom: 30px;">';
                    $mensaje .= '<a href="' . $url_restablecimiento . '" style="display: inline-block; background-color: #337ab7; color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">Restablecer mi contraseña</a>';
                    $mensaje .= '</p>';
                    $mensaje .= '<p style="margin-bottom: 20px; font-size: 0.9em; color: #777;">Si el botón no funciona, puedes copiar y pegar este enlace en tu navegador:</p>';
                    $mensaje .= '<p><a href="' . $url_restablecimiento . '" style="color: #337ab7; text-decoration: underline; font-size: 0.9em;">' . $url_restablecimiento . '</a></p>';
                    $mensaje .= '<p style="margin-top: 40px; margin-bottom: 15px; font-size: 0.85em; color: #999;">Este enlace es válido por 1 hora.</p>';
                    $mensaje .= '<p style="font-size: 0.85em; color: #999;">Si no solicitaste este restablecimiento, puedes ignorar este correo electrónico. Tu cuenta permanecerá segura.</p>';
                    $mensaje .= '<hr style="border: 0; border-top: 1px solid #e0e0e0; margin: 30px 0;">';
                    $mensaje .= '<p style="font-size: 0.8em; color: #999; text-align: center;">Atentamente,<br>El equipo de Sport Lite</p>';
                    $mensaje .= '</div>';
                    $mensaje .= '</body>';
                    $mensaje .= '</html>';

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
                        $mail->CharSet    = 'UTF-8'; 

                        // Remitentes y destinatarios
                        $mail->setFrom('sportliteoficial@gmail.com', 'Sport Lite');
                        $mail->addAddress($usuario['email'], $usuario['email']);

                        // Contenido del correo electrónico
                        $mail->isHTML(true); 
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