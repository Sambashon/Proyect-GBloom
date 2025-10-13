<?php
include "../../../clases/lobby.php";
include "../../filters.php";

$lobby = new Lobby();

$filtroCodigoPartida = new GError(
    "Código Partida",
    GError::notFound,
    GError::inclusive,
    [
        fn() => isset($_COOKIE["golden-code"]) && !empty(trim($_COOKIE["golden-code"]))
    ],
    "Validación Código Partida",
    true,
    "No se ha encontrado el codigo de acceso de la partida"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    $filtroCodigoPartida->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $codigo = $_COOKIE["golden-code"];
    
    $accion = $lobby->desconectaLobby($token, $codigo);
    echo json_encode($lobby->returnSuccess(null));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>