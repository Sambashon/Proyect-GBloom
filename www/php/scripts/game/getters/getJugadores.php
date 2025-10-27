<?php
include "../includeAll.php";
include "../../filters.php";

$partida = new Partida();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $jugadores = $partida->getJugadores($token)["result"];
    
    echo json_encode($partida->returnSuccess($jugadores));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>