<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
try {
    $token = $_COOKIE["golden-token"];
    $credentials = $perfil->getUserCredentials($token);

    echo json_encode($perfil->returnSuccess($credentials["result"]));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>