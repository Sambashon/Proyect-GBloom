<?php
include "../includeAll.php";
include "../../filters.php";

$partida = new Partida();
$inventario = new Inventario();
$lobby = new Lobby();
$dado = new Dado();

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    
    $turnos = $partida->getTurnoActual($token)["result"];
    if ($turnos == 6) {
        $partida->pasarFase($token);
        $inventario->reponerInventarios($token);
    } else {
        $inventario->cambiarInventarios($token);
    }
    $dado->setupDado($token);
    $partida->pasarTurno($token);
    $partida->empezarTurno($token);
    
    echo json_encode($partida->returnSuccess(null));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>