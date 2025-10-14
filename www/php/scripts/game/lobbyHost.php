<?php
include "../../clases/lobby.php";
include "../filters.php";

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
    $filtroMetodoGet->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    $filtroCodigoPartida->filter(null);
    
    $token = $_COOKIE["golden-token"];
    $codigo = $_COOKIE["golden-code"];
    
    $jugadores = $lobby->getLobbyUsuarios($codigo)["result"];
    $nombre = $lobby->getLobbyNombre($codigo)["result"];
    $host = $lobby->getLobbyHost($codigo)["result"];
    $comienza = $lobby->partidaComienza($codigo);

    include "../../../lobby/hostLobby/lobby.html";
} catch (Exception $e) {
    include "../../../error/400.html";
}
?>