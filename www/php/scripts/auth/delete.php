<?php
include "../../clases/perfil.php";
include "../filters.php";

$perfil = new Perfil();

try {
    $filtroMetodoPost->filter($_SERVER["REQUEST_METHOD"]);
        
    $token = isset($_COOKIE["golden-token"]) ? $_COOKIE["golden-token"] : "";
    $perfil->eliminarUsuario($token);
    
    echo json_encode($perfil->returnSuccess(null));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>