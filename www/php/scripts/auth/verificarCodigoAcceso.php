<?php
include "../game/includeAll.php";

$perfil = new Perfil();
$request = $_SERVER["REQUEST_METHOD"];

try { 
    if (isset($_GET["codigo"])) {
        $codigo = $_GET["codigo"];
        $accion = $perfil->verificarCodigoAcceso($codigo);

        echo json_encode($perfil->returnSuccess(null));
    } else {
        echo json_encode(["status" => 400, "ErrMessage" => "Link Invalido"]);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>