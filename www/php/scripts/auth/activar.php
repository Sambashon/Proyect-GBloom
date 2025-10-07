<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
$request = $_SERVER["REQUEST_METHOD"];
$host = $_SERVER['SERVER_NAME'];

switch ($request) {
    case "GET":
        if (isset($_GET["codigo"])) {
            $codigo = $_GET["codigo"];
            $accion = $perfil->activarCuenta($codigo);

            if ($accion["state"] == "success") {
                header("Location: https://" . $host);
            } else {
                echo "Link Invalido";
            }
            break;
        } else {
            echo "Link Invalido";
        }
        break;
    default:
        echo "Metodo de Acceso pormedio de GET unicamente";
        break;
}
?>