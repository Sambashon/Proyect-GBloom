<?php
include "../../clases/draftosaurus.php";

$draft = new Draftosaurus();
$request = $_SERVER["REQUEST_METHOD"];

switch ($request) {
    case "POST":
        try {
            $body = json_decode(file_get_contents('php://input'), true);
        } catch (Exception $e) {
            echo json_encode(["state" => "forbidden", "ErrMessage" => "El formato de entrada no es valido"]);
            break;
        }
            
        if (isset($body["codigo"])) {
            $codigo = $body["codigo"];
            $solicitud = $draft->verificarCodigo($codigo);

            setcookie('golden-code', $body["codigo"], [
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
            ]);
            
            echo json_encode($solicitud);
        } else {
            echo json_encode(["state" => "notFound", "ErrMessage" => "No se han encontrado los parametros necesarios para la accion solicitada"]);
        }
        break;
    default:
        echo json_encode(["state" => "forbidden", "ErrMessage" => "El metodo utilizado para la solicitud es invalida. Pofavor use POST"]);
        break;
}

?>