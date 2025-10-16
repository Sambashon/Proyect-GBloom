<?php
include "../game/includeAll.php";

$perfil = new Perfil();
if (isset($_COOKIE["golden-token"])) {
    $token = $_COOKIE["golden-token"];
    $perfil->cerrarSesion($token);
} else {
    echo json_encode(["status" => "notFound", "ErrMessage" => "El token de sesion requerido no se encuentra registrado"]);
}
?>