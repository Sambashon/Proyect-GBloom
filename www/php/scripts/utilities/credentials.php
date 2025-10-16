<?php
include "../game/includeAll.php";

$perfil = new Perfil();
try {
    $token = isset($_COOKIE["golden-token"]) ? $_COOKIE["golden-token"] : "";
    $credentials = $perfil->getUserCredentials($token);

    echo json_encode($perfil->returnSuccess($credentials["result"]));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>