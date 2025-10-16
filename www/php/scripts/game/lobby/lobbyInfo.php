<?php
include "../includeAll.php";
include "../../filters.php";

$lobby = new Lobby();

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
    
    $jugadores = $lobby->getLobbyUsuarios($codigo)["result"];
    $cantidad = $lobby->getCantidadMaxima($codigo)["result"];
    $nombre = $lobby->getLobbyNombre($codigo)["result"];
    $host = $lobby->getLobbyHost($codigo)["result"];
    $comienza = $lobby->partidaComienza($codigo);
    echo json_encode($lobby->returnSuccess(["codigo" => $codigo, "nombre" => $nombre, "host" => $host, "jugadores" => $jugadores, "cantidadJugadores" => $cantidad, "comienza" => $comienza]));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>