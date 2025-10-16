<?php
include "includeAll.php";
include "../filters.php";

$lobby = new Lobby();

$filtroCodigoPartida = new GError(
    "Código Partida",
    GError::notFound,
    GError::inclusive,
    [
        fn() => (isset($_GET["codigo"]) && !empty(trim($_GET["codigo"]))) || 
                (isset($_COOKIE["golden-code"]) && !empty(trim($_COOKIE["golden-code"])))
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
    
    if (isset($_GET["codigo"]) && !empty(trim($_GET["codigo"]))) {
        $codigo = trim($_GET["codigo"]);
        $solicitud = $lobby->verificarCodigo($codigo);

        setcookie('golden-code', $codigo, [
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        $host = $_SERVER['SERVER_NAME'];
        header("Location: https://" . $host . "/lobby/guestLobby/lobby.html");
    } else {
        $codigo = $_COOKIE["golden-code"];
    }

    $lobbyHost = $lobby->getLobbyHost($codigo)["result"];

    include "../../../lobby/guestLobby/lobby.html";
} catch (Exception $e) {
    include "../../../error/400.html";
}
?>