<?php
include "../../../clases/draftosaurus.php";

$draft = new Draftosaurus();
$request = $_SERVER["REQUEST_METHOD"];

switch ($request) {
    case "POST":
        if (isset($_COOKIE["golden-token"])) {
            $token = $_COOKIE["golden-token"];
            $accion = $draft->colocarDinosaurioNulls($token);

            echo json_encode($accion);
        } else {
            echo json_encode(["state" => "notFound", "ErrMessage" => "El token de sesion requerido no se encuentra registrado"]);
            break;
        }
        break;
    default:
        echo json_encode(["state" => "forbidden", "ErrMessage" => "El metodo utilizado para la solicitud es invalida. Pofavor use GET"]);
        break;
}

?>