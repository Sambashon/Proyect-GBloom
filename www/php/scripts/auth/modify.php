<?php
include "../game/includeAll.php";
include "../filters.php";

$perfil = new Perfil();

$filtroTokenSesion = new GError(
    "Token de Sesión",
    GError::notFound,
    GError::inclusive,
    [
        fn($input) => isset($_COOKIE["golden-token"])
    ],
    "Validación Token",
    true,
    "El token de sesion requerido no se encuentra registrado"
);

$filtroParametrosOpcionales = new GError(
    "Parámetros Opcionales",
    GError::notFound,
    GError::inclusive,
    [
        "al_menos_un_parametro" => function($data) {
            return isset($data["username"]) || 
                   isset($data["contraseña"]) || 
                   isset($data["correo"]) || 
                   isset($data["fechaNacimiento"]) || 
                   isset($data["descripcion"]);
        }
    ],
    "Validación Parámetros Edición",
    true,
    "No se han encontrado los parametros necesarios para la accion solicitada"
);

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
    $filtroTokenSesion->filter(true);
    
    $bodyInput = file_get_contents('php://input');
    $filtroBodyJSON->filter($bodyInput);
    $body = json_decode($bodyInput, true);
    
    $filtroParametrosOpcionales->filter($body);
    
    $token = $_COOKIE["golden-token"];
    $credentials = $perfil->getUserCredentials($token);
    $usuario = $credentials["result"];
    
    $cambios = [];
    if (isset($body["username"])) $cambios["username"] = $body["username"];
    if (isset($body["contraseña"])) $cambios["contraseña"] = $body["contraseña"];
    if (isset($body["correo"])) $cambios["correo"] = $body["correo"];
    if (isset($body["fechaNacimiento"])) $cambios["fechaNacimiento"] = $body["fechaNacimiento"];
    if (isset($body["descripcion"])) $cambios["descripcion"] = $body["descripcion"];
    
    $perfil->editarUsuario($token, $cambios);
    
    echo json_encode($perfil->returnSuccess(null));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>