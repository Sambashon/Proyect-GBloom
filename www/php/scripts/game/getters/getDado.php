<?php
include "../includeAll.php";
include "../../filters.php";

$dado = new Dado();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(true);
    
    $token = $_COOKIE["golden-token"];
    $accion = $dado->getDado($token);
    
    echo json_encode($dado->returnSuccess($accion));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>