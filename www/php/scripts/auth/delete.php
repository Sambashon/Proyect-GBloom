<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
if (isset($_COOKIE["golden-token"])) {
    $token = $_COOKIE["golden-token"];
    $perfil->eliminarUsuario($token);

    echo json_encode($perfil->returnSuccess(null));
} else {
    echo json_encode(["status" => "notFound", "ErrMessage" => "El token de sesion requerido no se encuentra registrado"]);
}
?>