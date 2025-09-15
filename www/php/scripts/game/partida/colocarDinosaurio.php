<?php
include "../../../clases/draftosaurus.php";

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
                
            if (isset($body["dinosaurioId"]) && isset($body["recinto"])) {
                $dinosaurioId = $body["dinosaurioId"];
                $recinto = $body["recinto"];
                $solicitud = $draft->getDado($token);
                if ($solicitud["state"] == "success") {
                    if ($solicitud["result"] == true) {
                        $dadoId = $solicitud["result"];

                        $accion = $draft->colocarDinosaurio($token, $dinosaurioId,  $recinto, $dadoId);
                        echo json_encode($accion);
                    } else {
                        echo json_encode(["state" => "notFound", "ErrMessage" => "No se a tirado el dado todavia"]);
                    }
                    
                } else {
                    echo json_encode(["state" => "notFound", "ErrMessage" => "No se a podido obtener el dado de la partida"]);
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
        echo json_encode(["state" => "forbidden", "ErrMessage" => "El metodo utilizado para la solicitud es invalida. Pofavor use GET"]);
        break;
}

?>