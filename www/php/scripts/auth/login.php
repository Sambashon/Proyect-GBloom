<?php
include "../../clases/perfil.php";
include "../filters.php";

$perfil = new Perfil();

$filtroParametros = new GError(
    "Parámetros Requeridos",
    GError::notFound,
    GError::all_match,
    [
        "El parametro 'username' es requerido" => fn($data) => isset($data['username']),
        "El parametro 'contraseña' es requerido" => fn($data) => isset($data['contraseña']),
        "El parametro 'mantener' es requerido" => fn($data) => isset($data['mantener'])
    ],
    "Validación Parámetros Login",
    true
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    
    $bodyInput = file_get_contents('php://input');
    $filtroBodyJSON->filter($bodyInput);
    $body = json_decode($bodyInput, true);
    
    $filtroParametros->filter($body);
    
    $username = trim($body["username"]);
    $contraseña = $body["contraseña"];
    $mantener = $body["mantener"];
    
    $filtroSesionActiva->filter(["username" => $username, "perfil" => $perfil]);
    
    if (isset($_COOKIE["golden-token"])) {
        $perfil->cerrarSesion($_COOKIE["golden-token"]);
    }
    
    $login = $perfil->accederUsuario($username, $contraseña, $mantener);
    $token = $login["result"]["token"];
    $expira = $login["result"]["expira"];
    
    setcookie('golden-token', $token, [
        'expires' => $expira->getTimestamp(),
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    echo json_encode($perfil->returnSuccess(null));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>