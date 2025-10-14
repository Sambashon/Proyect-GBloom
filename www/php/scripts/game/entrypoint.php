<?php
include "../../clases/partida.php";
include "../filters.php";

$partida = new Partida();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $partidaId = $partida->getPartidaJugando($token)["result"];
    
    include "../../../game/gameGuest/game.html";
} catch (Exception $e) {
    include "../../../error/403.html";
}
?>