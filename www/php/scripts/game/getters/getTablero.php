<?php
include "../includeAll.php";
include "../../filters.php";

$tablero = new Tablero();
$request = $_SERVER["REQUEST_METHOD"];

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);

    $token = $_COOKIE["golden-token"];
    $contenido = $tablero->getTablero($token);

    echo json_encode($tablero->returnSuccess($contenido));
} catch (Exception $e) {
    echo json_encode($e->getMessage());
}

?>