<?php
include "../includeAll.php";
include "../../filters.php";

$inventario = new Inventario();

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(true);
    
    $token = $_COOKIE["golden-token"];
    $accion = $inventario->getInventario($token);
    
    echo json_encode($inventario->returnSuccess($accion));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>