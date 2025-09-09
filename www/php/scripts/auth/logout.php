<?php
include "../../clases/perfil.php";

$perfil = new Perfil();
$token = $_COOKIE["golden-token"];
$perfil->cerrarSesion($token);
?>