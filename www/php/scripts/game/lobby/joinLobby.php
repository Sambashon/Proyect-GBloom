<?php
include "../../../clases/lobby.php";
include "../../filters.php";

$lobby = new Lobby();

$filtroCodigoDisponible = new GError(
    "Código Disponible",
    GError::notFound,
    GError::inclusive,
    [
        "codigo_en_body" => fn($input) => isset($input['body']['codigo']) && !empty(trim($input['body']['codigo'])),
        "codigo_en_cookie" => fn($input) => isset($_COOKIE["golden-code"]) && !empty(trim($_COOKIE["golden-code"]))
    ],
    "Validación Código Disponible",
    true,
    "No se ha encontrado el codigo de acceso de la partida"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $bodyInput = file_get_contents('php://input');
    $filtroBodyJSON->filter($bodyInput);
    $body = json_decode($bodyInput, true);
    
    $inputData = ['body' => $body];
    $filtroCodigoDisponible->filter($inputData);
    
    $token = $_COOKIE["golden-token"];
    
    $codigo = isset($body["codigo"]) && !empty(trim($body["codigo"])) 
        ? $body["codigo"] 
        : $_COOKIE["golden-code"];

    $accion = $lobby->conectaLobby($token, $codigo);
    echo json_encode($lobby->returnSuccess(null));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>