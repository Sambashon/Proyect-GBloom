<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
$request = $_SERVER["REQUEST_METHOD"];
$host = $_SERVER['SERVER_NAME'];

try { 
    if (isset($_GET["codigo"])) {
        $codigo = $_GET["codigo"];
        $accion = $perfil->activarCuenta($codigo);

        header("Location: https://" . $host . "/auth/login.html");
    } else {
        echo "Link Invalido";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>