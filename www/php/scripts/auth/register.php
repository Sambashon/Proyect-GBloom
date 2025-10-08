<?php
include "../../clases/perfil.php";
include "../filters.php";

$perfil = new Perfil();
$mailer = new GBMailer();
$request = $_SERVER["REQUEST_METHOD"];

$filtroParametros = new GError(
    "Parámetros Requeridos",
    GError::notFound,
    GError::all_match,
    [
        "El parametro 'username' es requerido" => fn($data) => isset($data['username']),
        "El parametro 'correo' es requerido" => fn($data) => isset($data['correo']),
        "El parametro 'contraseña' es requerido" => fn($data) => isset($data['contraseña'])
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
    
    $username = $body["username"];
    $contraseña = $body["contraseña"];
    $correo = $body["correo"];

    $register = $perfil->registrarUsuario($username, $contraseña, $correo);

    $codigo = $register["result"];   
    $mailer->correoBienvenida($correo, $username, $codigo);

    echo json_encode($perfil->returnSuccess(null));
} catch (Exception $e) {
    echo $e->getMessage();
}

?>