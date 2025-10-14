<?php
include "../../../clases/partida.php";
include "../../filters.php";

$partida = new Partida();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $partidaId = $partida->getPartidaJugando($token)["result"];
    $isHost = $partida->verificarHost($partidaId, $token);
    
    echo json_encode($partida->returnSuccess(null));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>