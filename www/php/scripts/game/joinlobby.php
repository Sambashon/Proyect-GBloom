<?php
include "../../clases/draftosaurus.php";

$draft = new Draftosaurus();
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
            
            if (isset($body["codigo"])) {
                $codigo = $body["codigo"];
                $accion = $draft->conectaLobby($token, $codigo);

                echo json_encode($accion);
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