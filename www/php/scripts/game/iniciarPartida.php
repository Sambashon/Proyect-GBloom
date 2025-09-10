<?php
include "../../clases/draftosaurus.php";

$draft = new Draftosaurus();
$request = $_SERVER["REQUEST_METHOD"];

switch ($request) {
    case "POST":
        if (isset($_COOKIE["golden-token"])) {
            $token = $_COOKIE["golden-token"];
            
            if (isset($_COOKIE["golden-code"])) {
                $codigo = $_COOKIE["golden-code"];
                $accion = $draft->iniciarPartida($token, $codigo);

                if ($accion["state"] == "success") {
                    $accion = $draft->setupInventarios($token);
                    
                    if ($accion["state"] == "success") {
                        $accion = $draft->setupOrdenJugadores($token);

                        echo json_encode($accion);
                    } else {
                        echo json_encode($accion);
                    }
                } else {
                    echo json_encode($accion);
                }
            } else {
                echo json_encode(["state" => "notFound", "ErrMessage" => "No se ha encontrado el codigo de acceso de la partida"]);
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