<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
$mailer = new GBMailer();
$request = $_SERVER["REQUEST_METHOD"];

switch ($request) {
    case "POST":
        try {
            $body = json_decode(file_get_contents('php://input'), true);
        } catch (Exception $e) {
            echo json_encode(["state" => "forbidden", "ErrMessage" => "El formato de entrada no es valido"]);
            break;
        }

        if (isset($body["username"]) && isset($body["correo"]) && isset($body["fechaNacimiento"]) && isset($body["contraseña"])) {
            $username = $body["username"];
            $contraseña = $body["contraseña"];
            $correo = $body["correo"];
            $fechaNacimiento = new DateTime($body["fechaNacimiento"]);

            $register = $perfil->registrarUsuario($username, $contraseña, $correo, $fechaNacimiento);
            
            if ($register["state"] == "success") {
                $mailer->correoBienvenida($correo, $username);
                echo json_encode(["state" => "success"]);
                break;
            } else {
                echo json_encode($register);
                break;
            }

        } else {
            echo json_encode(["state" => "notFound", "ErrMessage" => "No se han encontrado los parametros necesarios para la accion solicitada"]);
        }
        
        break;
    default:
        echo json_encode(["state" => "forbidden", "ErrMessage" => "El metodo utilizado para la solicitud es invalida. Pofavor use POST"]);
        break;
}

?>