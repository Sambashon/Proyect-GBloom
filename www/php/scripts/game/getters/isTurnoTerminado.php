<?php
include "../includeAll.php";
include "../../filters.php";

$partida = new Partida();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $over = $partida->isTurnoTerminado($token)["result"];
    
    echo json_encode($partida->returnSuccess($over));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>