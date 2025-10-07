<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
$request = $_SERVER["REQUEST_METHOD"];

switch ($request) {
    case "POST":
        try {
            $body = json_decode(file_get_contents('php://input'), true);
        } catch (Exception $e) {
            echo json_encode(["state" => "forbidden", "ErrMessage" => "El formato de entrada no es valido"]);
            break;
        }

        if (isset($body["username"]) && isset($body["contraseña"]) && isset($body["mantener"])) {
            $username = $body["username"];
            $contraseña = $body["contraseña"];
            $mantener = $body["mantener"];

            $username = trim($username);

            if (isset($_COOKIE["golden-token"])) {
                $token = $_COOKIE["golden-token"];
                $credentials = $perfil->getUserCredentials($token);
                if ($credentials["state"] == "success") {
                    if ($credentials["result"]["username"] == $username) {
                        echo json_encode(["state" => "forbidden", "ErrMessage" => "Ya existe una sesion activa para el nombre usuario brindado"]);
                        break;
                    } else {
                        $perfil->cerrarSesion($token);
                    }
                }
            }

            $login = $perfil->accederUsuario($username, $contraseña, $mantener);
            if ($login["state"] == "success") {
                $token = $login["result"]["token"];
                $expira = $login["result"]["expira"];
                setcookie('golden-token', $token, [
                    'expires' => $expira->getTimestamp(),
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                echo json_encode(["state" => "success"]);
                break;
            } else {
                echo json_encode($login);
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