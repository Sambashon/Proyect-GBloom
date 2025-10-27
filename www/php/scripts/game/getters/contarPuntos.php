<?php
include "../includeAll.php";
include "../../filters.php";

$tablero = new Tablero();
$partida = new Partida();
$request = $_SERVER["REQUEST_METHOD"];

try {
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);

    $token = $_COOKIE["golden-token"];
    $partida->contarPuntosJugadores($token, $tablero);

    echo json_encode($tablero->returnSuccess(null));
} catch (Exception $e) {
    echo json_encode($e->getMessage());
}

?>