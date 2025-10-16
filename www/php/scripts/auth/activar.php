<?php
include "../game/includeAll.php";

$perfil = new Perfil();
$request = $_SERVER["REQUEST_METHOD"];
$host = $_SERVER['SERVER_NAME'];

try { 
    if (isset($_GET["codigo"])) {
        $codigo = $_GET["codigo"];
        $accion = $perfil->activarCuenta($codigo);

        header("Location: https://" . $host . "/auth/login.html");
    } else {
        include "../../../error/400.html";
    }
} catch (Exception $e) {
    include "../../../error/notLink.html";
}
?>