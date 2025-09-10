<?php
include "gbloomdb.php";
class Draftosaurus extends GBloomDB {
    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    }

    // Metodos para comenzar una partida de Draftosaurus

    public function verificarHost(int $partidaId, string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $usuario = $credentials["result"];
            $solicitud = $this->pdo->prepare("select host from partida where id = :id;");
            $solicitud->execute(["id" => $partidaId]);
            $host = $solicitud->fetch();
            if ($host !== false) {
                if ($host == $usuario["username"]) {
                    return [
                        "state" => "success"
                    ];
                } else {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => "El host de la partida no coincide con el usuario brindado"
                    ];
                }
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El id de partida indexada no existe"
                ];
            }
        } else {
            return $credentials;
        }
    }

    public function getUltimaPartida(string $nombre, string $host): array {
        $solicitud = $this->pdo->prepare("select id, fecha, horaInicio from partida where nombre = :nombre and host = :host;");
        $solicitud->execute(["nombre" => $nombre, "host" => $host]);
        $partidas = $solicitud->fetchAll();

        if (empty($partidas)) {
            return [
                "status" => "notFound",
                "ErrMessage" => "No se ha encontrado ninguna partida con el nombre ni host brindado"
            ];
        }

        $utlimaPartida = $partidas[0];

        for ($i = 0; $i < count($partidas); $i++) {
            if (new DateTime($partidas[$i]["fecha"]) < new DateTime($utlimaPartida["fecha"])) {
                if (new DateTime($partidas[$i]["horaInicio"] < new DateTime($utlimaPartida["horaInicio"]))) {
                    $utlimaPartida = $partidas[$i];
                }
            }
        }

        return [
            "state" => "success",
            "result" => $utlimaPartida
        ];
    }

    public function crearPartida(string $nombre, string $host, int $cantidadJugadores, string $modoJuego): array {
        $insertar = $this->pdo->prepare("insert into partida(host, nombre, fecha, cantidadJugadores, modoJuego, turnoActual, faseActual) values (:host, :nombre, CURDATE(), :cantidad, :modo, 1, 1);");
        $insertar->execute(["host" => $host, "nombre" => $nombre, "cantidad" => $cantidadJugadores, "modo" => $modoJuego]);
        
        $partida = $this->getUltimaPartida($nombre, $host);
        if ($partida["state" == "success"]) {
            return [
                "state" => "success",
                "result" => $partida["id"]
            ];
        } else {
            return $partida;
        }
    }

    private function generarCodigoAcceso(): string {
        $caracteres = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $max = strlen($caracteres) - 1;
        $codigo = "";

        for ($i = 0; $i < 9; $i++) {
            $codigo .= $caracteres[mt_rand(0, $max)];
        }

        return $caracteres;
    }

    public function crearLobby(string $nombre, string $host): array {
        do {
            $codigo = $this->generarCodigoAcceso();

            $solicitud = $this->pdo->prepare("select codigo from lobby where codigo = :codigo;");
            $solicitud->execute(["codigo" => $codigo]);
            $existe = $solicitud->fetch();
        } while ($existe === false);

        $solicitud = $this->getUltimaPartida($nombre, $host);
        if ($solicitud["state"] == "success") {
            $partida = $solicitud["result"];
            $cantidadJugadores = $partida["cantidadJugadores"];
            $partidaId = $partida["id"];

            $ahora = new DateTime();
            $expira = $ahora->modify("+60 minutes")->format("Y-m-d H:i:s");

            $insertar = $this->pdo->prepare("insert into lobby(codigo, partidaId, cantidadJugadores) values (:codigo, :partidaId, :cantidad);");
            $insertar->execute(["codigo" => $codigo, "cantidad" => $cantidadJugadores, "expira" => $expira, "partidaId" => $partidaId]);

            return [
                "state" => "success"
            ];
        } else {
            return $solicitud;
        }
    }
    // Nota: Luego tengo que considerar si es mejpr y practico utilizar el id de partda nada mas.

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

    // Metodos para administar una partida de Draftosaurus

    public function conectaPartida(string $codigo): array {
        $solicitud = $this->pdo->prepare("select username from conecta where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $usuarios = $solicitud->fetchAll();
        if (!empty($usuarios)) {
            $solicitud = $this->pdo->prepare("select partidaId from lobby where codigo = :codigo;");
            $solicitud->execute(["codigo" => $codigo]);
            $partidaId = $solicitud->fetch();

            if ($partidaId === false) {
                for ($i = 0; $i < count($usuarios); $i++) {
                    $username = $usuarios[$i]["username"];

                    $actualizar = $this->pdo->prepare("update juega set jugando = false where username = :username;");
                    $actualizar->execute(["username" => $username]);

                    $insertar = $this->pdo->prepare("insert into juega(username, partidaId, jugando) values (:username, :partidaId, true);");
                    $insertar->execute(["username" => $username, "partidaId" => $partidaId]);
                }
                return [
                    "state" => "success"
                ];
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "No se ha podido encontrar el lobby utilizando el codigo de accesso brindado" 
                ];
            }
        } else {
            return [
                "state" => "notFound",
                "ErrMessage" => "No se han encontrado usuarios conectados actualmente al lobby indicado"
            ];
        }
    }

    public function iniciarPartida(string $token, string $codigo): array {
        $solicitud = $this->pdo->prepare("select partidaId from lobby where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $partidaId = $solicitud->fetch();

        if ($partidaId !== false) {
            $partidaId = $partidaId["partidaId"];
            $isHost = $this->verificarHost($partidaId, $token);
            if ($isHost["state"] == "success") {
                $actualizar = $this->pdo->prepare("update partida set horaInicio = CURTIME() where id = :id;");
                $actualizar->execute(["id" => $partidaId]);

                $actualizar = $this->pdo->prepare("update lobby set comienza = true where codigo = :codigo;");
                $actualizar->execute(["codigo" => $codigo]);

                return [
                    "state" => "success"
                ];
            } else {
                return $isHost;
            }
        } else {
            return [
                "state" => "notFound",
                "ErrMessage" => "No se ha podido encontrar el lobby utilizando el codigo de accesso brindado" 
            ];
        }
    }

    public function crearInventarios(array $usuarios): array {
        $meeples = [10, 10, 10, 10, 10, 10];
        $jugadores = [];

        for ($i = 0; $i < count($usuarios); $i++) {
            $jugador = [0, 0, 0, 0, 0, 0];

            foreach (range(1, 6) as $j) {
                $disponibles = array_keys(array_filter($meeples, function($m) { 
                    return $m > 0;
                }));

                if (empty($disponibles)) {
                    break;
                }

                $meeple = $disponibles[array_rand($disponibles)];

                $jugador[$meeple]++;
                $meeples[$meeple]--;
            }

            $jugadores[] = [
               "dinosaurios" => [
                    "amarillo" => $jugador[0],
                    "azul" => $jugador[1],
                    "rojo" => $jugador[2],
                    "morado" => $jugador[3],
                    "verde" => $jugador[4],
                    "naranja" => $jugador[5]
               ],
               "username" => $usuarios[$i]["username"]
            ];
        }

        return $jugadores;
    }

    // Nota: No es 100% realista con respecto a la dinamica de tomar dinosaurios de la bolsa porque en la segunda parte de la partida no toma en cuenta la cantidad reducida de ciertos dinosaurios

    public function setupInventarios(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = true;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $isHost = $this->verificarHost($partidaId, $token);

                if ($isHost["state"] == "success") {
                    $solicitud = $this->pdo->prepare("select username from juega where partidaId = :partidaId;");
                    $solicitud->execute(["partidaId" => $partidaId]);
                    $usuarios = $solicitud->fetchAll();

                    if (!empty($usuarios)) {
                        $inventarios = $this->crearInventarios($usuarios);
                        foreach ($inventarios as $inventario) {
                            foreach ($inventario["dinosaurios"] as $id => $cantidad) {
                                $insertar = $this->pdo->prepare("insert into inventario(username, partidaId, dinosaurioId, cantidad) values (:username, :partidaId, :dinosaurioId, :cantidad);");
                                $insertar->execute(["username" => $inventario["username"], "partidaId" => $partidaId, "dinosaurioId" => $id, "cantidad" => $cantidad]);
                            }
                        }

                        return [
                            "state" => "success"
                        ];
                    } else {
                        return [
                            "state" => "notFound",
                            "ErrMessage" => "No hay usuarios conectados a la partida indexada"
                        ];
                    }
                } else {
                    return $isHost;
                }
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
                ];
            }
        } else {
            return $credentials;
        }
    }

    public function setupOrdenJugadores(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = true;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $isHost = $this->verificarHost($partidaId, $token);

                if ($isHost["state"] == "success") {
                    $solicitud = $this->pdo->prepare("
                        select usuario.fechaNacimiento, juega.username from juega join usuario 
                        on usuario.username = juega.username 
                        where juega.partidaId = :partidaId 
                        order by usuario.fechaNacimiento desc;"
                    );
                    $solicitud->execute(["partidaId" => $partidaId]);
                    $usuarios = $solicitud->fetchAll();

                    if (!empty($usuarios)) {
                        for ($i = 0; $i < count($usuarios); $i++) {
                            $actualizar = $this->pdo->prepare("update juega set numJugador = :orden where username = :username and jugando = true;");
                            $actualizar->execute(["orden" => $i + 1, "username" => $usuarios[$i]["username"]]);
                        }

                        return [
                            "state" => "success"
                        ];
                    } else {
                        return [
                            "state" => "notFound",
                            "ErrMessage" => "No hay usuarios conectados a la partida indexada"
                        ];
                    }
                } else {
                    return $isHost;
                }
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
                ];
            }
        } else {
            return $credentials;
        }
    }

    public function getInventario(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = true;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from inventario where partidaId = :partidaId and username = :username;");
                $solicitud->execute(["partidaId" => $partidaId, "username" => $username]);
                $inventario = $solicitud->fetchAll();

                if (!empty($inventarioInfo)) {
                    return [
                        "state" => "success",
                        "result" => $inventario
                    ];
                } else {
                    return [
                        "state" => "notFound",
                        "ErrMessage" => "El inventario de juego se encuentra actualmente vacio"
                    ];
                }
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
                ];
            }
        } else {
            return $credentials;
        }
    }

}