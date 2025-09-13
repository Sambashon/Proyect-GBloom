<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
$request = $_SERVER["REQUEST_METHOD"];

switch ($request) {
    case "POST":
        if (isset($_COOKIE["golden-token"])) {
            $token = $_COOKIE["golden-token"];

            try {
                $body = json_decode(file_get_contents('php://input'), true);
            } catch (Exception $e) {
                echo json_encode(["state" => "forbidden", "ErrMessage" => "El formato de entrada no es valido"]);
                break;
            }

            if (isset($body["username"]) || isset($body["contraseña"]) || isset($body["correo"]) || isset($body["fechaNacimiento"]) || isset($body["descripcion"])) {
                $credentials = $perfil->getUserCredentials($token);
                if ($credentials["state"] == "success") {
                    $usuario = $credentials["result"];

                    if (isset($body["username"])) {
                        $usuario["username"] = $body["username"];
                    }
                    if (isset($body["contraseña"])) {
                        $usuario["contraseña"] = $body["contraseña"];
                    }
                    if (isset($body["correo"])) {
                        $usuario["correo"] = $body["correo"];
                    }
                    if (isset($body["fechaNacimiento"])) {
                        $usuario["fechaNacimiento"] = $body["fechaNacimiento"];
                    }
                    if (isset($body["descripcion"])) {
                        $usuario["descripcion"] = $body["descripcion"];
                    }

                    $perfil->editarUsuario($token, $usuario);
                    
                    return [
                        "state" => "success"
                    ];
                } else {
                    return $credentials;
                }
            } else {
                echo json_encode(["state" => "notFound", "ErrMessage" => "No se han encontrado los parametros necesarios para la accion solicitada"]);
            }
        } else {
            echo json_encode(["state" => "notFound", "ErrMessage" => "El token de sesion requerido no se encuentra registrado"]);
            break;
        }

        break;
    default:
        echo json_encode(["state" => "forbidden", "ErrMessage" => "El metodo utilizado para la solicitud es invalida. Pofavor use POST"]);
        break;
}

?>