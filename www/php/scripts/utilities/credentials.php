<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
if (isset($_COOKIE["golden-token"])) {
    $token = $_COOKIE["golden-token"];
    $credentials = $perfil->getUserCredentials($token);

    if ($credentials["state"] == "success") {
        echo json_encode([
            "state" => "success",
            "result" => $credentials["result"]
        ]);
    } else {
        echo json_encode($credentials);
    }
} else {
    echo json_encode(["state" => "notFound", "ErrMessage" => "El token de sesion requerido no se encuentra registrado"]);
}
?>