<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
if (isset($_COOKIE["golden-token"])) {
    $token = $_COOKIE["golden-token"];
    $perfil->cerrarSesion($token);
} else {
    echo json_encode(["state" => "notFound", "ErrMessage" => "El token de sesion requerido no se encuentra registrado"]);
}
?>