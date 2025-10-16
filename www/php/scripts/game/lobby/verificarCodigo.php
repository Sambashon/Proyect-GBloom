<?php
include "../includeAll.php";
include "../../filters.php";

$lobby = new Lobby();

$filtroParametrosCodigo = new GError(
    "Parámetros Código",
    GError::notFound,
    GError::all_match,
    [
        "codigo requerido" => fn($data) => isset($data["codigo"]) && !empty(trim($data["codigo"]))
    ],
    "Validación Parámetros Código",
    true,
    "No se han encontrado los parametros necesarios para la accion solicitada"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    
    $bodyInput = file_get_contents('php://input');
    $filtroBodyJSON->filter($bodyInput);
    $body = json_decode($bodyInput, true);
    
    $filtroParametrosCodigo->filter($body);
    
    $codigo = $body["codigo"];
    $solicitud = $lobby->verificarCodigo($codigo);

    setcookie('golden-code', $codigo, [
        'expires' => time() + 86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    echo json_encode($solicitud);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>