<?php
include "../../clases/perfil.php";
include "../filters.php";

$perfil = new Perfil();

$filtroParametroCodigo = new GError(
    "Parámetro Código",
    GError::notFound,
    GError::inclusive,
    [
        fn($input) => isset($input["codigo"]) && !empty($input["codigo"])
    ],
    "Validación Parámetro Código",
    true,
    "El parámetro 'codigo' es requerido y no puede estar vacío"
);

$filtroBodyContraseña = new GError(
    "Body con Contraseña",
    GError::notFound,
    GError::inclusive,
    [
        "Contraseña no presente" => function($data) {
            return isset($data["contraseña"]) && !empty(trim($data["contraseña"]));
        }
    ],
    "Validación Nueva Contraseña",
    true,
    "La nueva contraseña es requerida y no puede estar vacía"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroParametroCodigo->filter($_GET);
    
    $bodyInput = file_get_contents('php://input');
    $filtroBodyJSON->filter($bodyInput);
    $body = json_decode($bodyInput, true);
    
    $filtroBodyContraseña->filter($body);
    
    $codigo = $_GET["codigo"];
    $nuevaContraseña = trim($body["contraseña"]);
    
    $accion = $perfil->cambiarContraseña($codigo, $nuevaContraseña);
    
    echo json_encode($perfil->returnSuccess(null));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>