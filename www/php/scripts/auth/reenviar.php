<?php
include "../game/includeAll.php";
include "../filters.php";

$perfil = new Perfil();
$mailer = new GBMailer();
$request = $_SERVER["REQUEST_METHOD"];

$filtroParametros = new GError(
    "Parámetros Requeridos",
    GError::notFound,
    GError::all_match,
    [
        "Se requiere el parámetro 'username' o 'email'" => fn($data) => isset($data['username']) || isset($data['email']),
    ],
    "Validación Parámetros",
    true
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    
    $bodyInput = file_get_contents('php://input');
    $filtroBodyJSON->filter($bodyInput);
    $body = json_decode($bodyInput, true);
    
    $filtroParametros->filter($body);
    
    if (isset($body['username'])) {
        $username = $body['username'];
        $correo = $perfil->getCorreoAsociado($username)["result"];
    } else {
        $correo = $body['email'];
        $username = $perfil->getUsuarioAsociado($correo)["result"];
    }

    $expira = new DateTime();
    $expira->modify("+15 minutes");
    $expira = $expira->format("Y-m-d H:m:s");
    $codigo = $perfil->generarCodigoAcceso();
        
    $perfil->registrarCodigoTemporal($codigo, $username, $expira);
    $mailer->correoBienvenida($correo, $username, $codigo);

    echo json_encode($perfil->returnSuccess(null));
} catch (Exception $e) {
    echo $e->getMessage();
}

?>