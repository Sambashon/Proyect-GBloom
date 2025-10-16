<?php
include "../includeAll.php";
include "../../filters.php";

$partida = new Partida();

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    
    $accion = $partida->terminarPartidaActiva($token);
    echo json_encode($partida->returnSuccess(null));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>