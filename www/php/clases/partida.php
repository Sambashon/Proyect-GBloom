<?php
include "gbloomdb.php";

class Partida extends GBloomDB {

    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    }

    public function verificarHost(int $partidaId, string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == self::SUCCESS) {
            $usuario = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select host from partida where id = :id;");
            $solicitud->execute(["id" => $partidaId]);
            $host = $solicitud->fetch();
            if ($host !== false) {
                $host = $host["host"];

                if ($host == $usuario) {
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
        $solicitud = $this->pdo->prepare("select id, fecha, horaInicio, cantidadJugadores from partida where nombre = :nombre and host = :host order by fecha desc limit 1;");
        $solicitud->execute(["nombre" => $nombre, "host" => $host]);
        $solicitud = $solicitud->fetch();

        if ($solicitud !== false) {
            $partida = $solicitud;
            return [
                "state" => "success",
                "result" => $partida
            ];
        } else {
            return [
                "status" => "notFound",
                "ErrMessage" => "No se ha encontrado ninguna partida con el nombre ni host brindado"
            ];
        }
    }

    public function crearPartida(string $nombre, string $token, int $cantidadJugadores, string $modoJuego): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $host = $credentials["result"]["username"];

            $error = false;
            $errMessages = [];
            if (strlen($nombre) > 32) {
                $error = true;
                $errMessages[] = "El nombre de la partida no puede ser mayor a 32 caracteres";
            }
            if ($cantidadJugadores > 5 || $cantidadJugadores < 2) {
                $error = true;
                $errMessages[] = "La cantidad de jugadores brindada es invalida. Debe ser al menos 2 hasta maximo 5";
            }
            if ($modoJuego != "virtual" && $modoJuego != "seguimiento") {
                $error = true;
                $errMessages[] = "El modo de juego brindado es invalido. Debe ser 'virtual' o 'seguimiento'";
            }
            if ($error) {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => $errMessages
                ];
            }

            $insertar = $this->pdo->prepare("insert into partida(host, nombre, fecha, cantidadJugadores, modoJuego, turnoActual, faseActual) values (:host, :nombre, CURDATE(), :cantidad, :modo, 1, 1);");
            $insertar->execute(["host" => $host, "nombre" => $nombre, "cantidad" => $cantidadJugadores, "modo" => $modoJuego]);
            
            $partida = $this->getUltimaPartida($nombre, $host);
            if ($partida["state"] == "success") {
                return [
                    "state" => "success",
                    "result" => $partida["result"]
                ];
            } else {
                return $partida;
            }
        } else {
            return $credentials;
        }
    }

    public function eliminarPartida(string $nombre, string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $host = $credentials["result"]["username"];
            $partida = $this->getUltimaPartida($nombre, $host);
            $partidaId = $partida["result"]["id"];
            $solicitud = $this->pdo->prepare("select codigo from lobby where partidaId = :id;");
            $solicitud->execute(["id" => $partidaId]);
            $codigo = $solicitud->fetch();

            if ($codigo !== false) {
                $codigo = $codigo["codigo"];
                $eliminar = $this->pdo->prepare("delete from conecta where codigo = :codigo");
                $eliminar->execute(["codigo" => $codigo]);

                $eliminar = $this->pdo->prepare("delete from lobby where codigo = :codigo");
                $eliminar->execute(["codigo" => $codigo]);
            }

            $eliminar = $this->pdo->prepare("delete from inventario where partidaId = :id");
            $eliminar->execute(["id" => $partidaId]);

            $eliminar = $this->pdo->prepare("delete from tablero where partidaId = :id");
            $eliminar->execute(["id" => $partidaId]);

            $eliminar = $this->pdo->prepare("delete from juega where partidaId = :id");
            $eliminar->execute(["id" => $partidaId]);

            $eliminar = $this->pdo->prepare("delete from partida where id = :id");
            $eliminar->execute(["id" => $partidaId]);

            return [
                "state" => "success"
            ];
        } else {
            return $credentials;
        }
    }

    public function conectaPartida(string $codigo): array {
        $solicitud = $this->pdo->prepare("select username from conecta where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $usuarios = $solicitud->fetchAll();
        if (!empty($usuarios)) {
            $solicitud = $this->pdo->prepare("select partidaId from lobby where codigo = :codigo;");
            $solicitud->execute(["codigo" => $codigo]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];

                for ($i = 0; $i < count($usuarios); $i++) {
                    $username = $usuarios[$i]["username"];

                    $actualizar = $this->pdo->prepare("update juega set jugando = 0 where username = :username;");
                    $actualizar->execute(["username" => $username]);

                    $insertar = $this->pdo->prepare("insert into juega(username, partidaId, jugando) values (:username, :partidaId, 1);");
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

                $actualizar = $this->pdo->prepare("update lobby set comienza = 1 where codigo = :codigo;");
                $actualizar->execute(["codigo" => $codigo]);

                $accion = $this->conectaPartida($codigo);
                if ($accion["state"] == "success") {
                    $accion = $this->conectaPartidaNulls($codigo);

                    return $accion;
                } else {
                    return $accion;
                }
                
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

    public function terminarPartidaActiva(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $host = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select id from partida where horaFinal is null and host = :host order by fecha desc limit 1;");
            $solicitud->execute(["host" => $host]);
            $solicitud = $solicitud->fetch();

            if ($solicitud !== false) {
                $partidaId = $solicitud["id"];

                $actualizar = $this->pdo->prepare("update partida set horaFinal = CURTIME() where id = :id");
                $actualizar->execute(["id" => $partidaId]);

                return [
                    "state" => "success"
                ];
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El usuario brindado no hostea ninguna partida activa"
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
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
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
                            $actualizar = $this->pdo->prepare("update juega set numJugador = :orden where username = :username and jugando = 1;");
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

    public function isTurnoTerminado(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $solicitud = $solicitud->fetch();

            if ($solicitud !== false) {
                $partidaId = $solicitud["partidaId"];
                $solicitud = $this->pdo->prepare("select estado from juega where partidaId = :partidaId;");
                $solicitud->execute(["partidaId" => $partidaId]);
                $solicitud = $solicitud->fetchAll();

                if (!empty($solicitud)) {
                    foreach ($solicitud as $usuario => $estado) {
                        if ($estado == "jugandoTurno") {
                            return [
                                "state" => "success",
                                "result" => false
                            ];
                        }
                    }

                    return [
                        "state" => "success",
                        "result" => true
                    ];
                } else {
                    return [
                        "state" => "notFound",
                        "ErrMessage" => "No se han encontrado jugadores dentro de la partida"
                    ];
                }

                if ($turnoActual == $numJugador) { 
                    return [
                        "state" => "success"
                    ];
                } else {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => "El jugador brindado no le toca turno para tirar el dado"
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

    public function pasarTurno(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $isHost = $this->verificarHost($partidaId, $token);

                if ($isHost["state"] == "success") {
                    $solicitud = $this->pdo->prepare("select turnoActual from partida where partidaId = :partidaId;");
                    $solicitud->execute(["partidaId" => $partidaId]);
                    $solicitud = $solicitud->fetch();

                    if ($solicitud !== false) {
                        $turnoActual = intval($solicitud["turnoActual"]);

                        if ($turnoActual < 6) {
                            $turnoActual++;
                        } else {
                            $turnoActual = 1;
                        }

                        $actualizar = $this->pdo->prepare("update partida set turnoActual = :turnoActual where id = :id");
                        $actualizar->execute(["turnoActual" => $turnoActual, "id" => $partidaId]);

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

    public function pasarFase(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $isHost = $this->verificarHost($partidaId, $token);

                if ($isHost["state"] == "success") {
                    $solicitud = $this->pdo->prepare("select faseActual from partida where partidaId = :partidaId;");
                    $solicitud->execute(["partidaId" => $partidaId]);
                    $solicitud = $solicitud->fetch();

                    if ($solicitud !== false) {
                        $faseActual = intval($solicitud["faseActual"]);

                        if ($faseActual < 2) {
                            $faseActual++;
                        } else {
                            return [
                                "state" => "forbidden",
                                "ErrMessage" => "Ya termino la fase 2, ya no se puede pasar de fase nuevamente, la partida termino"
                            ];
                        }

                        $actualizar = $this->pdo->prepare("update partida set turnoActual = :turnoActual where id = :id");
                        $actualizar->execute(["turnoActual" => $faseActual, "id" => $partidaId]);

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

    public function contarPuntosJugadores(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
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
                        $tableros = [];
                        for ($i = 0; $i < count($usuarios); $i++) {
                            $solicitud = $this->pdo->prepare("select t.* from tablero t join juega j on t.username = j.username where j.partidaId = :partidaId and j.numJugador = :numJugador;");
                            $solicitud->execute(["partidaId" => $partidaId, "numJugador" => $i + 1]);
                            $solicitud = $solicitud->fetchAll();

                            if (!empty($solicitud)) {
                                $tableros[] = $solicitud;
                            } else {
                                return [
                                    "state" => "notFound",
                                    "ErrMessage" => "No se pudo obtener el inventario solicitado"
                                ];
                            }
                        }

                        $tablerosContados = [];
                        foreach ($tableros as $tablero) {
                            $tablerosContados[] = $this->contarPuntosTablero($tablero);
                        }

                        foreach ($tablerosContados as $tableroContado) {
                            foreach ($tableros as $tablero) {
                                if ($this->contarDinosauriosTablero($tablero, $tableroContado["rey"]) > $tableroContado["reyCounter"]) {
                                    $tableroContado["puntos"] -= 7;
                                }
                            }
                        }

                        foreach ($tablerosContados as $tableroContado) {
                            $actualizar = $this->pdo->prepare("update juega set puntos = :puntos where username = :username and partidaId = :partidaId;");
                            $actualizar->execute(["puntos" => $tableroContado["puntos"], "username" => $tableroContado["username"], "partidaId" => $partidaId]);
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

    public function empezarTurno(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];

                $actualizar = $this->pdo->prepare("update juega set estado = :estado where username = :username and partidaId = :partidaId;");
                $actualizar->execute(["estado" => "jugandoTurno", "partidaId" => $partidaId, "username" => $username]);

                return [
                    "state" => "success"
                ];
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

    public function terminarTurno(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];

                $actualizar = $this->pdo->prepare("update juega set estado = :estado where username = :username and partidaId = :partidaId;");
                $actualizar->execute(["estado" => "turnoTerminado", "partidaId" => $partidaId, "username" => $username]);

                return [
                    "state" => "success"
                ];
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

    public function getTurnoActual(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];

                $solicitud = $this->pdo->prepare("select turnoActual from partida where id = :id;");
                $solicitud->execute(["id" => $partidaId]);
                $solicitud = $solicitud->fetch();

                if ($solicitud !== false) {
                    return [
                        "state" => "success",
                        "result" => $solicitud["turnoActual"]
                    ];
                } else {
                    return [
                        "state" => "notFound",
                        "ErrMessage" => "Error al buscar partida id"
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

    public function getFaseActual(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];

                $solicitud = $this->pdo->prepare("select faseActual from partida where id = :id;");
                $solicitud->execute(["id" => $partidaId]);
                $solicitud = $solicitud->fetch();

                if ($solicitud !== false) {
                    return [
                        "state" => "success",
                        "result" => $solicitud["faseActual"]
                    ];
                } else {
                    return [
                        "state" => "notFound",
                        "ErrMessage" => "Error al buscar partida id"
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

    public function isPartidaOver(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];

                $solicitud = $this->pdo->prepare("select horaFinal from partida where id = :id;");
                $solicitud->execute(["id" => $partidaId]);
                $solicitud = $solicitud->fetch();

                if ($solicitud !== false) {
                    $horaFinal = $solicitud["horaFinal"];
                    if ($horaFinal != "NULL") {
                        return [
                            "state" => "success",
                            "result" => true
                        ];
                    } else {
                        return [
                            "state" => "success",
                            "result" => false
                        ];
                    }
                    
                } else {
                    return [
                        "state" => "notFound",
                        "ErrMessage" => "Error al buscar partida id"
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