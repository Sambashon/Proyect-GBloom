<?php
include "../includeAll.php";
include "../../filters.php";

$partida = new Partida();
$inventario = new Inventario();
$lobby = new Lobby();
$dado = new Dado();

$filtroCodigoPartida = new GError(
    "Código Partida",
    GError::notFound,
    GError::inclusive,
    [
        fn() => isset($_COOKIE["golden-code"]) && !empty(trim($_COOKIE["golden-code"]))
    ],
    "Validación Código Partida",
    true,
    "No se ha encontrado el codigo de acceso de la partida"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    $filtroCodigoPartida->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $codigo = $_COOKIE["golden-code"];
    
    $accion = $partida->iniciarPartida($token, $codigo);
    $accion = $inventario->setupInventarios($token);
    $accion = $partida->setupOrdenJugadores($token);
    $accion = $dado->setupDado($token);
    $partida->empezarTurno($token);
    
    echo json_encode($accion);
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>