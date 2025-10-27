<?php
include "../includeAll.php";
include "../../filters.php";

$dado = new Dado();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $over = $dado->isTurnoTirarDado($token)["result"];
    
    echo json_encode($dado->returnSuccess($over));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>