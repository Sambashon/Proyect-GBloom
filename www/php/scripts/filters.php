<?php
$filtroMetodoPost = new GError(
    "Método HTTP",
    GError::forbidden,
    GError::inclusive,
    [
        "Metodo invalido" => fn($input) => $input === "POST"
    ],
    "Validación Método HTTP",
    true,
    "El metodo utilizado para la solicitud es invalido. Por favor use POST"
);

$filtroMetodoGet = new GError(
    "Método HTTP",
    GError::forbidden,
    GError::inclusive,
    [
        "metodo_invalido" => fn($input) => $input === "GET"
    ],
    "Validación Método HTTP",
    true,
    "El metodo utilizado para la solicitud es invalido. Por favor use GET"
);

$filtroBodyJSON = new GError(
    "Body JSON", 
    GError::forbidden,
    GError::inclusive,
    [
        "json_invalido" => function($input) {
            try {
                json_decode($input, true);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    ],
    "Validación JSON",
    true, 
    "El formato de entrada no es valido"
);

$filtroSesionActiva = new GError(
    "Sesión Activa",
    GError::forbidden,
    GError::exclusive,
    [
        "sesion_activa_mismo_usuario" => function($input) {
            if (!isset($_COOKIE["golden-token"])) {
                return false;
            }
            
            $token = $_COOKIE["golden-token"];
            try {
                $credentials = $input["perfil"]->getUserCredentials($token);
                return $credentials["result"]["username"] === $input['username'];
            } catch (Exception $e) {
                return false;
            }
        }
    ],
    "Validación Sesión Activa",
    true,
    "Ya existe una sesion activa para el nombre usuario brindado"
);
?>