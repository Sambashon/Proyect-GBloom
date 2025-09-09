<?php
include "php/clases/perfil.php";

$perfil = new Perfil();
$token = $_COOKIE["golden-token"];
$perfil->eliminarUsuario($token);

?>