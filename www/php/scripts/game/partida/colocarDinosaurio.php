<?php
include "../includeAll.php";
include "../../filters.php";

$dado = new Dado();
$tablero = new Tablero();

$filtroDinosaurioParams = new GError(
    "Parámetros Dinosaurio",
    GError::notFound,
    GError::all_match,
    [
        "dinosaurioId" => fn($data) => isset($data['dinosaurioId']),
        "recinto" => fn($data) => isset($data['recinto'])
    ],
    "Validación Parámetros Colocar",
    true,
    "No se han encontrado los parametros necesarios para la accion solicitada"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(null);
    $filtroBodyJSON->filter(file_get_contents('php://input'));
    
    $token = $_COOKIE["golden-token"];
    $body = json_decode(file_get_contents('php://input'), true);
    
    $filtroDinosaurioParams->filter($body);
    
    $dadoId = $dado->getDado($token)["result"];
    $accion = $tablero->colocarDinosaurio($token, $body["dinosaurioId"], $body["recinto"], $dadoId);
    
    echo json_encode($tablero->returnSuccess(null));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>