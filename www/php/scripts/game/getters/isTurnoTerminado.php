<?php
include "../includeAll.php";
include "../../filters.php";

$partida = new Partida();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $turnos = array_column($partida->getJugadores($token)["result"], "estado");
    $turnosTerminados = array_filter($turnos, function($estado) {
        return $estado == "turnoTerminado";
    });
    $over = $partida->isTurnoTerminado($token)["result"];
    
    echo json_encode($partida->returnSuccess(["turnoTerminado" => $over, "cantidad" => count($turnosTerminados)]));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>