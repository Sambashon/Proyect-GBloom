<?php
include "includeAll.php";
include "../filters.php";

$partida = new Partida();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $over = $partida->isPartidaOver($token)["result"];
    if ($over) {
        include "../../../error/403.html";
    } else {
        include "../../../game/game.html";
    }
    
} catch (Exception $e) {
    include "../../../error/403.html";
}
?>