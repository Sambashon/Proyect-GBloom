<?php
include "/php/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;

class GBMailer {
    private PHPMailer $mailer;
    private string $mail;
    private string $password;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->mail = "gbloomenterprise@gmail.com";
        $this->password = "lrma qpiz abap imvs";

        $this->mailer->isSMTP();
        $this->mailer->Host = "smtp.gmail.com";
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->mail;
        $this->mailer->Password = $this->password;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        $this->mailer->isHTML(true);
        $this->mailer->setFrom("gbloomenterprise@gmail.com", "Golden Blossom Enterprise");

    }

    public function enviarCorreo(string $asunto, string $destinatario, string $contenido) {
        try {
            $this->mailer->addAddress($destinatario, "Golden Blossom Client");
            $this->mailer->Subject = $asunto;
            $this->mailer->Body = $contenido;
            $this->mailer->send();

            return [
                "state" => "success"
            ];
        } catch (Exception $e) {
            return [
                "state" => "errorFound",
                "ErrMessage" => "No se ha podido enviar el correo: " . $this->mailer->ErrorInfo
            ];
        }
    }

    public function correoBienvenida(string $correo, string $usuario, string $codigo) {
        $host = $_SERVER['SERVER_NAME'];
        $contenido = <<<EOF
            <!DOCTYPE html>
            <html lang="es">
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Bienvenido a Draftosaurus</title>
            <style>
                body {
                font-family: Arial, sans-serif;
                background-color: #f0f4f8;
                margin: 0;
                padding: 0;
                color: #333;
                }
                .container {
                max-width: 600px;
                margin: 30px auto;
                background-color: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                overflow: hidden;
                }
                .header {
                background-color: #D59F0A;
                color: white;
                padding: 20px;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                }
                .content {
                padding: 20px;
                line-height: 1.6;
                }
                .button {
                display: inline-block;
                margin: 20px 0;
                padding: 12px 25px;
                background-color: #D59F0A;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: bold;
                }
                .footer {
                background-color: #f0f4f8;
                color: #666;
                text-align: center;
                padding: 15px;
                font-size: 12px;
                }
            </style>
            </head>
            <body>
            <div class="container">
                <div class="header">
                ¡Bienvenido a Draftosaurus!
                </div>
                <div class="content">
                <p>Hola <strong>$usuario</strong>,</p>
                <p>Gracias por unirte a <strong>Draftosaurus</strong>. Estamos emocionados de tenerte en nuestra comunidad de parques de dinosaurios. Prepárate para competir con tus amigos y demostrar quien hace el mejor parque.</p>
                <p>Para empezar tu aventura, haz clic en el botón de abajo para activar su cuenta de usuario:</p>
                <a href="https://$host/auth/activar?codigo=$codigo" class="button">Activar Usuario</a>
                <p>Si tienes alguna duda, no dudes en contactarnos. ¡Diviértete y que tus dinosaurios dominen el juego!</p>
                <p>El equipo de GBloom</p>
                </div>
                <div class="footer">
                Este correo es automático, por favor no respondas. <br>
                © 2025 Golden Blossom. Todos los derechos reservados.
                </div>
            </div>
            </body>
            </html>
        EOF;

        $this->enviarCorreo("Te damos la bienvenida a Draftosaurus!!!", $correo, $contenido);
    }

    public function cambiosCuenta($correo, $usuario) {
        $contenido = <<<EOF
            <!DOCTYPE html>
            <html lang="es">
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Alerta de cambios en tu cuenta - Draftosaurus</title>
            <style>
                body {
                font-family: Arial, sans-serif;
                background-color: #f0f4f8;
                margin: 0;
                padding: 0;
                color: #333;
                }
                .container {
                max-width: 600px;
                margin: 30px auto;
                background-color: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                overflow: hidden;
                }
                .header {
                background-color: #D59F0A;
                color: white;
                padding: 20px;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                }
                .content {
                padding: 20px;
                line-height: 1.6;
                }
                .alert {
                background-color: #ffe4b3;
                border-left: 6px solid #D59F0A;
                padding: 15px;
                margin: 20px 0;
                border-radius: 6px;
                }
                .button {
                display: inline-block;
                margin: 20px 0;
                padding: 12px 25px;
                background-color: #D59F0A;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: bold;
                }
                .footer {
                background-color: #f0f4f8;
                color: #666;
                text-align: center;
                padding: 15px;
                font-size: 12px;
                }
            </style>
            </head>
            <body>
            <div class="container">
                <div class="header">
                ⚠️ Cambios detectados en tu cuenta
                </div>
                <div class="content">
                <p>Hola <strong>$usuario</strong>,</p>
                <p>Queremos informarte que recientemente se han realizado <strong>cambios en los datos de tu cuenta</strong> de Draftosaurus.</p>
                <div class="alert">
                    Si fuiste tú quien realizó estos cambios, no necesitas hacer nada más.<br>
                    Pero si <strong>no reconoces esta actividad</strong>, te recomendamos que revises tu cuenta de inmediato.
                </div>
                <p>Puedes acceder a tu cuenta desde el siguiente enlace seguro:</p>
                <a href="https://goldenblossom.ddns.net/" class="button">Revisar mi cuenta</a>
                <p>Por tu seguridad, nunca compartas tu contraseña y asegúrate de usar una clave única y segura.</p>
                <p>El equipo de GBloom</p>
                </div>
                <div class="footer">
                Este correo es automático, por favor no respondas. <br>
                © 2025 Golden Blossom. Todos los derechos reservados.
                </div>
            </div>
            </body>
            </html>
            EOF;
        
        $this->enviarCorreo("Cambios en su cuenta de Draftosaurus", $correo, $contenido);
    }

    public function cuentaEliminada($correo, $usuario) {
        $contenido = <<<EOF
            <!DOCTYPE html>
            <html lang="es">
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tu cuenta ha sido eliminada - Draftosaurus</title>
            <style>
                body {
                font-family: Arial, sans-serif;
                background-color: #f0f4f8;
                margin: 0;
                padding: 0;
                color: #333;
                }
                .container {
                max-width: 600px;
                margin: 30px auto;
                background-color: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                overflow: hidden;
                }
                .header {
                background-color: #D59F0A;
                color: white;
                padding: 20px;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                }
                .content {
                padding: 20px;
                line-height: 1.6;
                }
                .alert {
                background-color: #ffe4b3;
                border-left: 6px solid #D59F0A;
                padding: 15px;
                margin: 20px 0;
                border-radius: 6px;
                }
                .button {
                display: inline-block;
                margin: 20px 0;
                padding: 12px 25px;
                background-color: #D59F0A;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: bold;
                }
                .footer {
                background-color: #f0f4f8;
                color: #666;
                text-align: center;
                padding: 15px;
                font-size: 12px;
                }
            </style>
            </head>
            <body>
            <div class="container">
                <div class="header">
                Tu cuenta ha sido eliminada
                </div>
                <div class="content">
                <p>Hola <strong>$usuario</strong>,</p>
                <p>Te informamos que la cuenta asociada a este correo ha sido <strong>eliminada de Draftosaurus</strong>.</p>
                <div class="alert">
                    Si realizaste esta acción de forma intencional, no hay nada más que hacer.<br>
                    En caso contrario, puedes comunicarte con nosotros para revisar la situación.
                </div>
                <p>Nos gustaría agradecerte sinceramente por haber formado parte de <strong>Draftosaurus</strong>. Esperamos que hayas disfrutado tu experiencia y, si alguna vez deseas volver, ¡las puertas estarán abiertas para ti!</p>
                <p>El equipo de GBloom</p>
                </div>
                <div class="footer">
                Este correo es automático, por favor no respondas. <br>
                © 2025 Golden Blossom. Todos los derechos reservados.
                </div>
            </div>
            </body>
            </html>
        EOF;

        $this->enviarCorreo("Tu cuenta ha sido eliminada - Draftosaurus", $correo, $contenido);
    }

}