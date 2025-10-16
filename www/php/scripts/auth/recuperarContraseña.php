<?php
include "../game/includeAll.php";
include "../filters.php";

$perfil = new Perfil();

$filtroBodyCorreo = new GError(
    "Body con Contraseña",
    GError::notFound,
    GError::inclusive,
    [
        "Correo no presente" => function($data) {
            return isset($data["correo"]) && !empty(trim($data["correo"]));
        }
    ],
    "Validación Correo",
    true,
    "El correo es requerida y no puede estar vacío"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    
    $bodyInput = file_get_contents('php://input');
    $filtroBodyJSON->filter($bodyInput);
    $body = json_decode($bodyInput, true);
    $filtroBodyCorreo->filter($body);
    $correo = $body["correo"];
    
    $accion = $perfil->recuperarContraseña($correo);
    echo json_encode($perfil->returnSuccess(null));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>