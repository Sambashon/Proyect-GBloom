<?php
include "gbloomdb.php";
include "partida.php";

class Lobby extends GBloomDB {

    private Partida $partida;

    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    
        $this->partida = new Partida();
    }

    public function verificarCodigo($codigo): array {
        $solicitud = $this->pdo->prepare("select codigo from lobby where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $existe = $solicitud->fetch();

        if ($existe !== false) {
            return [
                "state" => "success"
            ];
        } else {
            return [
                "state" => "notFound",
                "ErrMessage" => "El codigo de acceso no se encuentra registrado en la base de datos"
            ];
        }
    }

    private function generarCodigoAcceso(): string {
        $caracteres = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $max = strlen($caracteres) - 1;
        $codigo = "";

        for ($i = 0; $i < 9; $i++) {
            $codigo .= $caracteres[mt_rand(0, $max)];
        }

        return $codigo;
    }

    public function crearLobby(string $nombre, string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $host = $credentials["result"]["username"];

            //do {
                // CAMBIAR ESTO PARA LA TERCERA ENTREGA O PARA MULTIJUGADOR
                $codigo = $this->generarCodigoAcceso();

                //$solicitud = $this->pdo->prepare("select codigo from lobby where codigo = :codigo;");
                //$solicitud->execute(["codigo" => $codigo]);
                //$existe = $solicitud->fetch();
            //} while ($existe !== false);

            $solicitud = $this->partida->getUltimaPartida($nombre, $host);
            if ($solicitud["state"] == "success") {
                $partida = $solicitud["result"];
                $cantidadJugadores = $partida["cantidadJugadores"];
                $partidaId = $partida["id"];

                $solicitud = $this->pdo->prepare("select partidaId from lobby where partidaId = :id;");
                $solicitud->execute(["id" => $partidaId]);
                $lobbyExiste = $solicitud->fetch();
                if ($lobbyExiste === false) {
                    $insertar = $this->pdo->prepare("insert into lobby(codigo, partidaId, cantidadJugadores) values (:codigo, :partidaId, :cantidad);");
                    $insertar->execute(["codigo" => $codigo, "cantidad" => $cantidadJugadores, "partidaId" => $partidaId]);

                    return [
                        "state" => "success"
                    ];
                } else {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => "Un lobby ya existe para esta partida"
                    ];
                }
            } else {
                return $solicitud;
            }
        } else {
            return $credentials;
        }
    }

    public function conectaLobby(string $token, string $codigo): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $solicitud = $this->pdo->prepare("select cantidadJugadores from lobby where codigo = :codigo");
            $solicitud->execute(["codigo" => $codigo]);
            $lobby = $solicitud->fetch();
            if ($lobby !== false) {
                $usuarios = $this->getLobbyUsuarios($codigo);
                if (count($usuarios) < $lobby["cantidadJugadores"]) {
                    $insertar = $this->pdo->prepare("insert into conecta(codigo, username) values (:codigo, :username);");
                    $insertar->execute(["codigo" => $codigo, "username" => $credentials["result"]["username"]]);
                    return [
                        "state" => "success"
                    ];
                } else {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => "No es posible conectarse debido a que la sala se encuentra llena en el momento"
                    ];
                }
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El codigo brindado no es invalido"
                ];
            }
        } else {
            return $credentials;
        }
    }

    public function desconectaLobby(string $token, string $codigo): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select codigo from lobby where codigo = :codigo");
            $solicitud->execute(["codigo" => $codigo]);
            $existe = $solicitud->fetch();
            if ($existe !== false) {
                $eliminar = $this->pdo->prepare("delete from conecta where username = :username and codigo = :codigo");
                $eliminar->execute(["username" => $username, "codigo" => $codigo]);
                
                return [
                    "state" => "success"
                ];
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El codigo brindado no es invalido"
                ];
            }
        } else {
            return $credentials;
        }
    }

    public function getLobbyUsuarios(string $codigo): array {
        $solicitud = $this->pdo->prepare("select username from conecta where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $usuarios = $solicitud->fetchAll();
        if (!empty($usuario)) {
            return [
                "state" => "success",
                "result" => $usuarios
            ];
        } else {
            return [
                "state" => "notFound",
                "ErrMessage" => "El codigo de acceso utilizado para indexar no existe en la base de datos"
            ];
        }
    }
}