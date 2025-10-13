<?php
include "../../../clases/lobby.php";
include "../../filters.php";

$partida = new Partida();
$lobby = new Lobby();

$filtroParametrosPartida = new GError(
    "Parámetros Partida",
    GError::notFound,
    GError::all_match,
    [
        "nombre requerido" => fn($data) => isset($data["nombre"]) && !empty(trim($data["nombre"])),
        "cantidadJugadores requerida" => fn($data) => isset($data["cantidadJugadores"]) && is_numeric($data["cantidadJugadores"]),
        "modo requerido" => fn($data) => isset($data["modo"]) && !empty(trim($data["modo"]))
    ],
    "Validación Parámetros Partida",
    true,
    "No se han encontrado los parametros necesarios para la accion solicitada"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    $token = $_COOKIE["golden-token"];
    
    $bodyInput = file_get_contents('php://input');
    $filtroBodyJSON->filter($bodyInput);
    $body = json_decode($bodyInput, true);
    
    $filtroParametrosPartida->filter($body);
    
    $nombre = trim($body["nombre"]);
    $cantidad = $body["cantidadJugadores"];
    $modo = trim($body["modo"]);

    $accion = $partida->crearPartida($nombre, $token, $cantidad, $modo);
    $codigo = $lobby->crearLobby($nombre, $token)["result"];

    setcookie('golden-code', $codigo, [
        'expires' => time() + 86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    echo json_encode($partida->returnSuccess($codigo));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>