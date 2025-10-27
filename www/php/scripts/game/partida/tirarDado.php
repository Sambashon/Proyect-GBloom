<?php
include "../includeAll.php";
include "../../filters.php";

$dado = new Dado();

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $accion = $dado->tirarDado($token)["result"];
    
    echo json_encode($dado->returnSuccess($accion));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>