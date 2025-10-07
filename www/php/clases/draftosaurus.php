<?php
include "gbloomdb.php";
class Draftosaurus extends GBloomDB {

    private const SUCCESS = "success";

    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    }

    // Metodos para comenzar una partida de Draftosaurus

    // Metodos para administar una partida de Draftosaurus



    // EXLUSIVO PARA LA SEGUNDA ENTREGA
    public function conectaPartidaNulls(string $codigo): array {
        $solicitud = $this->pdo->prepare("select partidaId from lobby where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $partidaId = $solicitud->fetch();

        if ($partidaId !== false) {
            $partidaId = $partidaId["partidaId"];

            $usuarios = ["null", "null2", "null3", "null4"];

            for ($i = 0; $i < count($usuarios); $i++) {
                $username = $usuarios[$i];

                $actualizar = $this->pdo->prepare("update juega set jugando = false where username = :username;");
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



    public function getInventario(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from inventario where partidaId = :partidaId and username = :username;");
                $solicitud->execute(["partidaId" => $partidaId, "username" => $username]);
                $inventario = $solicitud->fetchAll();

                if (!empty($inventario)) {
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

    // PARA LA SEGUNDA ENTREGA
    public function getInventarioNull(string $username): array {
        if ($username == "null" || $username == "null2" || $username == "null3" || $username == "null4") {
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from inventario where partidaId = :partidaId and username = :username;");
                $solicitud->execute(["partidaId" => $partidaId, "username" => $username]);
                $inventario = $solicitud->fetchAll();

                if (!empty($inventario)) {
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
            return [
                "state" => "nofound",
                "ErrMessage" => "El usuario null brindado no es valido"
            ];
        }
    }

    public function getTablero(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad, recinto from tablero where partidaId = :partidaId and username = :username;");
                $solicitud->execute(["partidaId" => $partidaId, "username" => $username]);
                $tablero = $solicitud->fetchAll();

                if (!empty($tablero)) {
                    return [
                        "state" => "success",
                        "result" => $tablero
                    ];
                } else {
                    return [
                        "state" => "notFound",
                        "ErrMessage" => "El tablero de juego se encuentra actualmente vacio"
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

    // PARA LA SEGUNDA ENTREGA
    public function getTableroNull(string $username): array {
        if ($username == "null" || $username == "null2" || $username == "null3" || $username == "null4") {
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad, recinto from tablero where partidaId = :partidaId and username = :username;");
                $solicitud->execute(["partidaId" => $partidaId, "username" => $username]);
                $tablero = $solicitud->fetchAll();

                if (!empty($tablero)) {
                    return [
                        "state" => "success",
                        "result" => $tablero
                    ];
                } else {
                    return [
                        "state" => "notFound",
                        "ErrMessage" => "El tablero de juego se encuentra actualmente vacio"
                    ];
                }
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
                ];
            }
        } else {
            return [
                "state" => "nofound",
                "ErrMessage" => "El usuario null brindado no es valido"
            ];
        }
    }

    public function colocarDinosaurio(string $token, string $dinosaurio, string $recinto, string $dadoId): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $solicitud = $this->pdo->prepare("select cantidad from inventario where partidaId = :partidaId and username = :username and dinosaurioId = :dinosaurioId;");
                $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]);
                $solicitud = $solicitud->fetch();

                if ($solicitud !== false) {
                    $cantidadDisponibles = $solicitud["cantidad"];
                    $cafeteria = false;
                    $baños = false;
                    $bosque = false;
                    $desierto = false;

                    if ($dinosaurio != "amarillo" && $dinosaurio != "azul" && $dinosaurio != "rojo" && $dinosaurio != "morado" && $dinosaurio != "verde" && $dinosaurio != "naranja") {
                        return [
                            "state" => "forbidden",
                            "ErrMessage" => "El id de dinosaurio brindado es invalido"
                        ];
                    }

                    if ($recinto != "igualdad" && $recinto != "desigualdad" && $recinto != "tres" && $recinto != "rio" && $recinto != "soledad" && $recinto != "monarquia" && $recinto != "romance") {
                        return [
                            "state" => "forbidden",
                            "ErrMessage" => "El recinto brindado es invalido"
                        ];
                    }

                    $tiroDado = $this->isTurnoTirarDado($token);
                    if ($tiroDado["state"] == "success") {
                        if (!$tiroDado["result"]) {
                            switch ($dadoId) {
                                case "cafeteria":
                                    $cafeteria = true;
                                    break;
                                case "baños":
                                    $baños =true;
                                    break;
                                case "bosque":
                                    $bosque = true;
                                    break;
                                case "desierto":
                                    $desierto = true;
                                    break;
                            }
                        } else {
                            $cafeteria = true;
                            $baños = true;
                            $bosque = true;
                            $desierto = true;
                        }
                    } else {
                        return $tiroDado;
                    }

                    $recintosProhibidos = [];
                    $tablero = $this->getTablero($token);

                    if ($tablero["state"] == "success") {
                        foreach ($tablero["result"] as $tableroRecinto) {
                            if ($tableroRecinto["dinosaurioId"] == "rojo" && $dadoId == "notrex" && !isset($recintosProhibidos[$tableroRecinto["recinto"]])) {
                                $recintosProhibidos[$tableroRecinto["recinto"]] = true;
                            }

                            if ($dadoId == "vacio" && !isset($recintosProhibidos[$tableroRecinto["recinto"]])) {
                                $recintosProhibidos[$tableroRecinto["recinto"]] = true;
                            }
                        }

                        if (isset($recintosProhibidos[$recinto])) {
                            return [
                                "state" => "notFound",
                                "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
                            ];
                        }
                    }

                    switch (true) {
                        case ($recinto == "igualdad") && ($bosque || $cafeteria):
                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                            $solicitud = $solicitud->fetch();
                            

                            if ($solicitud === false) {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);

                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            } else {
                                $cantidad = $solicitud["cantidad"];

                                if ($cantidad > 6) {
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                    ];
                                } else {
                                    $cantidad++;
                                }

                                if ($solicitud["dinosaurioId"] == $dinosaurio) {
                                    $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $recinto]);
                                    
                                    if ($cantidadDisponibles > 1) {
                                        
                                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId username = :username and partidaId = :partidaId;");
                                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                    } else {
                                        
                                        $eliminar = $this->pdo->prepare("delete inventario where dinosaurioId = :dinosaurioId username = :username and partidaId = :partidaId;");
                                        $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                    }

                                    return [
                                        "state" => "success"
                                    ];
                                } else {
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El recinto solo admite dinosaurios de la misma especie"
                                    ];
                                }
                            }
                        case ($recinto == "desigualdad") && ($desierto || $baños):
                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                            $solicitud = $solicitud->fetchAll();

                            if (empty($solicitud)) {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);

                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            } else {
                                $cantidad = 0;
                                foreach ($solicitud as $dinosaurioExiste) {
                                    $cantidad += $dinosaurioExiste["cantidad"];
                                    if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                        return [
                                            "state" => "forbidden",
                                            "ErrMessage" => "El recinto solo admite un dinosaurio de cada especie"
                                        ];
                                    }
                                }

                                if ($cantidad > 6) {
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                    ];
                                }

                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                
                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            }
                        case ($recinto == "soledad") && ($desierto || $baños):
                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                            $solicitud = $solicitud->fetchAll();

                            if (empty($solicitud)) {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                
                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            } else {
                                $cantidad = 0;
                                foreach ($solicitud as $dinosaurioExiste) {
                                    $cantidad += $dinosaurioExiste["cantidad"];
                                }

                                if ($cantidad > 1) {
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                    ];
                                }

                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                
                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            }
                        case ($recinto == "romance") && ($desierto || $cafeteria):
                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                            $solicitud = $solicitud->fetchAll();

                            if (empty($solicitud)) {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                
                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            } else {
                                $cantidad = 0;
                                $dinosaurios = 0;
                                foreach ($solicitud as $dinosaurioExiste) {
                                    if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                        $dinosaurios = $dinosaurioExiste["cantidad"];
                                    }
                                    $cantidad += $dinosaurioExiste["cantidad"];
                                }

                                if ($cantidad > 6) {
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                    ];
                                } else {
                                    $dinosaurios++;
                                }

                                if ($dinosaurios > 1) {
                                    $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $dinosaurios]);
                                } else {
                                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                }

                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }
                                
                                return [
                                    "state" => "success"
                                ];
                            }
                        case ($recinto == "monarquia") && ($baños || $bosque):
                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                            $solicitud = $solicitud->fetch();

                            if ($solicitud === false) {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                
                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            } else {
                                return [
                                    "state" => "forbidden",
                                    "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                ];
                            }
                        case ($recinto == "tres") && ($cafeteria || $bosque):
                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                            $solicitud = $solicitud->fetchAll();

                            if (empty($solicitud)) {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                
                                return [
                                    "state" => "success"
                                ];
                            } else {
                                $cantidad = 0;
                                $dinosaurios = 0;
                                foreach ($solicitud as $dinosaurioExiste) {
                                    if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                        $dinosaurios = $dinosaurioExiste["cantidad"];
                                    }
                                    $cantidad += $dinosaurioExiste["cantidad"];
                                }

                                if ($cantidad > 3) {
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                    ];
                                } else {
                                    $dinosaurios++;
                                }

                                if ($dinosaurios > 1) {
                                    $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $dinosaurios]);
                                } else {
                                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                }
                                
                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            }
                        case "rio":
                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                            $solicitud = $solicitud->fetchAll();

                            if (empty($solicitud)) {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                
                                return [
                                    "state" => "success"
                                ];
                            } else {
                                $cantidad = 0;
                                $dinosaurios = 0;
                                foreach ($solicitud as $dinosaurioExiste) {
                                    if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                        $dinosaurios = $dinosaurioExiste["cantidad"];
                                    }
                                    $cantidad += $dinosaurioExiste["cantidad"];
                                }

                                if ($cantidad > 6) {
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                    ];
                                } else {
                                    $dinosaurios++;
                                }

                                if ($dinosaurios > 1) {
                                    $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $dinosaurios]);
                                } else {
                                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                                }
                                
                                
                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            }
                        default:
                            return [
                                "state" => "forbidden",
                                "ErrMessage" => "No se ha podido colocar el dinosaurio brindado debido a las reestricciones del dadoo de colocacio"
                            ];
                    }
                } else {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => "El jugador no posee el dinosaurio en su inventario para realizar la accion solicitada"
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

    // PARA LA SEGUNDA ENTREGA
    public function colocarDinosaurioNull(string $username, string $dinosaurio, string $recinto, string $dadoId): array {
        if ($username != "null" && $username != "null2" && $username != "null3" && $username != "null4") {
            return [
                "state" => "forbidden",
                "ErrMessage" => "El null brindado no es valido"
            ];
        }

        $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        $partidaId = $solicitud->fetch();

        if ($partidaId !== false) {
            $partidaId = $partidaId["partidaId"];
            $solicitud = $this->pdo->prepare("select cantidad from inventario where partidaId = :partidaId and username = :username and dinosaurioId = :dinosaurioId;");
            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]);
            $solicitud = $solicitud->fetch();

            if ($solicitud !== false) {
                $cantidadDisponibles = $solicitud["cantidad"];
                $cafeteria = false;
                $baños = false;
                $bosque = false;
                $desierto = false;

                if ($dinosaurio != "amarillo" && $dinosaurio != "azul" && $dinosaurio != "rojo" && $dinosaurio != "morado" && $dinosaurio != "verde" && $dinosaurio != "naranja") {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => "El id de dinosaurio brindado es invalido"
                    ];
                }

                if ($recinto != "igualdad" && $recinto != "desigualdad" && $recinto != "tres" && $recinto != "rio" && $recinto != "soledad" && $recinto != "monarquia" && $recinto != "romance") {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => "El recinto brindado es invalido"
                    ];
                }

                $tiroDado = $this->isTurnoTirarDadoNull($username);
                if ($tiroDado["state"] == "success") {
                    if (!$tiroDado["result"]) {
                        switch ($dadoId) {
                            case "cafeteria":
                                $cafeteria = true;
                                break;
                            case "baños":
                                $baños =true;
                                break;
                            case "bosque":
                                $bosque = true;
                                break;
                            case "desierto":
                                $desierto = true;
                                break;
                        }
                    } else {
                        $cafeteria = true;
                        $baños = true;
                        $bosque = true;
                        $desierto = true;
                    }
                } else {
                    return $tiroDado;
                }

                $recintosProhibidos = [];
                $tablero = $this->getTableroNull($username);
                
                if ($tablero["state"] == "success") {
                    foreach ($tablero as $tableroRecinto) {
                        if ($tableroRecinto["dinosaurioId"] == "rojo" && $dadoId == "notrex" && !isset($recintosProhibidos[$tableroRecinto["recinto"]])) {
                            $recintosProhibidos[$tableroRecinto["recinto"]] = true;
                        }

                        if ($dadoId == "vacio" && !isset($recintosProhibidos[$tableroRecinto["recinto"]])) {
                            $recintosProhibidos[$tableroRecinto["recinto"]] = true;
                        }
                    }
                }

                if (isset($recintosProhibidos[$recinto])) {
                    return [
                        "state" => "notFound",
                        "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
                    ];
                }

                switch (true) {
                    case ($recinto == "igualdad") && ($bosque || $cafeteria):
                        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                        $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                        $solicitud = $solicitud->fetch();
                        

                        if ($solicitud === false) {
                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);

                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        } else {
                            $cantidad = $solicitud["cantidad"];

                            if ($cantidad > 6) {
                                return [
                                    "state" => "forbidden",
                                    "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                ];
                            } else {
                                $cantidad++;
                            }

                            if ($solicitud["dinosaurioId"] == $dinosaurio) {
                                $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $recinto]);
                                
                                if ($cantidadDisponibles > 1) {
                                    
                                    $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                                } else {
                                    
                                    $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                    $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                                }

                                return [
                                    "state" => "success"
                                ];
                            } else {
                                return [
                                    "state" => "forbidden",
                                    "ErrMessage" => "El recinto solo admite dinosaurios de la misma especie"
                                ];
                            }
                        }
                    case ($recinto == "desigualdad") && ($desierto || $baños):
                        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                        $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                        $solicitud = $solicitud->fetchAll();

                        if (empty($solicitud)) {
                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);

                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        } else {
                            $cantidad = 0;
                            foreach ($solicitud as $dinosaurioExiste) {
                                $cantidad += $dinosaurioExiste["cantidad"];
                                if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El recinto solo admite un dinosaurio de cada especie"
                                    ];
                                }
                            }

                            if ($cantidad > 6) {
                                return [
                                    "state" => "forbidden",
                                    "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                ];
                            }

                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            
                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        }
                    case ($recinto == "soledad") && ($desierto || $baños):
                        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                        $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                        $solicitud = $solicitud->fetchAll();

                        if (empty($solicitud)) {
                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            
                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        } else {
                            $cantidad = 0;
                            foreach ($solicitud as $dinosaurioExiste) {
                                $cantidad += $dinosaurioExiste["cantidad"];
                            }

                            if ($cantidad > 1) {
                                return [
                                    "state" => "forbidden",
                                    "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                ];
                            }

                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            
                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        }
                    case ($recinto == "romance") && ($desierto || $cafeteria):
                        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                        $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                        $solicitud = $solicitud->fetchAll();

                        if (empty($solicitud)) {
                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            
                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        } else {
                            $cantidad = 0;
                            $dinosaurios = 0;
                            foreach ($solicitud as $dinosaurioExiste) {
                                if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                    $dinosaurios = $dinosaurioExiste["cantidad"];
                                }
                                $cantidad += $dinosaurioExiste["cantidad"];
                            }

                            if ($cantidad > 6) {
                                return [
                                    "state" => "forbidden",
                                    "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                ];
                            } else {
                                $dinosaurios++;
                            }

                            if ($dinosaurios > 1) {
                                $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $dinosaurios]);
                            } else {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            }

                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }
                            
                            return [
                                "state" => "success"
                            ];
                        }
                    case ($recinto == "monarquia") && ($baños || $bosque):
                        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                        $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                        $solicitud = $solicitud->fetch();

                        if ($solicitud === false) {
                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            
                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        } else {
                            return [
                                "state" => "forbidden",
                                "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                            ];
                        }
                    case ($recinto == "tres") && ($cafeteria || $bosque):
                        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                        $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                        $solicitud = $solicitud->fetchAll();

                        if (empty($solicitud)) {
                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            
                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        } else {
                            $cantidad = 0;
                            $dinosaurios = 0;
                            foreach ($solicitud as $dinosaurioExiste) {
                                if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                    $dinosaurios = $dinosaurioExiste["cantidad"];
                                }
                                $cantidad += $dinosaurioExiste["cantidad"];
                            }

                            if ($cantidad > 3) {
                                return [
                                    "state" => "forbidden",
                                    "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                ];
                            } else {
                                $dinosaurios++;
                            }

                            if ($dinosaurios > 1) {
                                $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $dinosaurios]);
                            } else {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            }

                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }
                            
                            return [
                                "state" => "success"
                            ];
                        }
                    case "rio":
                        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                        $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                        $solicitud = $solicitud->fetchAll();

                        if (empty($solicitud)) {
                            $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                            $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            
                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        } else {
                            $cantidad = 0;
                            $dinosaurios = 0;
                            foreach ($solicitud as $dinosaurioExiste) {
                                if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                    $dinosaurios = $dinosaurioExiste["cantidad"];
                                }
                                $cantidad += $dinosaurioExiste["cantidad"];
                            }

                            if ($cantidad > 6) {
                                return [
                                    "state" => "forbidden",
                                    "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                                ];
                            } else {
                                $dinosaurios++;
                            }

                            if ($dinosaurios > 1) {
                                $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $dinosaurios]);
                            } else {
                                $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                                $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                            }
                            
                            if ($cantidadDisponibles > 1) {
                                
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                            } else {
                                
                                $eliminar = $this->pdo->prepare("delete from inventario where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                                $eliminar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                            }

                            return [
                                "state" => "success"
                            ];
                        }
                    default:
                        return [
                            "state" => "forbidden",
                            "ErrMessage" => "No se ha podido colocar el dinosaurio brindado debido a las reestricciones del dadoo de colocacio"
                        ];
                }
            } else {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => "El jugador no posee el dinosaurio en su inventario para realizar la accion solicitada"
                ];
            }
        } else {
            return [
                "state" => "notFound",
                "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
            ];
        }
    }

    // PARA LA SEGUNDA ENTREGA (quiero borrar todos los metodos de null, ocupan mucho codigo >:( )
    public function colocarDinosaurioNulls(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $partidaId = $solicitud->fetch();

            if ($partidaId !== false) {
                $partidaId = $partidaId["partidaId"];
                $isHost = $this->verificarHost($partidaId, $token);

                $usuarios = ["null", "null2", "null3", "null4"];
                if ($isHost["state"] == "success") {
                    $solicitud = $this->pdo->prepare("select dadoId from partida where id = :id;");
                    $solicitud->execute(["id" => $partidaId]);
                    $solicitud = $solicitud->fetch();

                    if ($solicitud !== false) {
                        $dadoId = $solicitud["dadoId"];
                        foreach ($usuarios as $null) {
                            $nullTablero = $this->getTableroNull($null);
                            $nullInventario = $this->getInventarioNull($null);
                            $recintosPermitidos = [];
                            $dinosaurios = [];
                            $jugadasPermitidas = [];

                            foreach ($nullInventario["result"] as $dinosaurio) {
                                $dinosaurios[] = $dinosaurio["dinosaurioId"];
                            }

                            switch ($dadoId) {
                                case "cafeteria":
                                    $recintosPermitidos = ["romance", "tres", "igualdad"];
                                    break;
                                case "baños":
                                    $recintosPermitidos = ["soledad", "desigualdad", "monarquia"];
                                    break;
                                case "bosque":
                                    $recintosPermitidos = ["tres", "igualdad", "monarquia"];
                                    break;
                                case "desierto":
                                    $recintosPermitidos = ["romance", "soledad", "desigualdad"];
                                    break;
                                case "vacio":
                                    $recintosPermitidos = ["romance", "tres", "igualdad", "soledad", "desigualdad", "monarquia"];
                                    $recintosProhibidos = [];

                                    if ($nullTablero["state"] == "success") {
                                        foreach ($nullTablero as $tableroRecinto) {
                                            if (!isset($recintosProhibidos[$tableroRecinto["recinto"]])) {
                                                $recintosProhibidos[$tableroRecinto["recinto"]] = true;
                                            }
                                        }
                                    }

                                    break;
                                case "notrex":
                                    $recintosPermitidos = ["romance", "tres", "igualdad", "soledad", "desigualdad", "monarquia"];
                                    $recintosProhibidos = [];

                                    if ($nullTablero["state"] == "success") {
                                        foreach ($nullTablero as $tableroRecinto) {
                                            if ($tableroRecinto["dinosaurioId"] == "rojo" && !isset($recintosProhibidos[$tableroRecinto["recinto"]])) {
                                                $recintosProhibidos[$tableroRecinto["recinto"]] = true;
                                            }
                                        }
                                    }

                                    break;
                                default:
                                    return [
                                        "state" => "forbidden",
                                        "ErrMessage" => "El id de dado es invalido"
                                    ];
                            }

                            $nuevosRecintosPermitidos = [];
                            foreach ($recintosPermitidos as $recinto) {
                                if (!isset($recintosProhibidos[$recinto])) {
                                    $nuevosRecintosPermitidos[] = $recinto;
                                }
                            }

                            $recintosPermitidos = $nuevosRecintosPermitidos;
                            foreach ($dinosaurios as $dinosaurio) {
                                foreach ($recintosPermitidos as $recinto) {
                                    switch ($recinto) {
                                        case "igualdad":
                                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                                            $solicitud = $solicitud->fetch();
                                            

                                            if ($solicitud === false) {
                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            } else {
                                                $cantidad = $solicitud["cantidad"];

                                                if ($cantidad > 6) {
                                                    break;
                                                } else {
                                                    $cantidad++;
                                                }

                                                if ($dinosaurios > 1) {
                                                    $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                                } else {
                                                    break;
                                                }
                                            }
                                            break;
                                        case "desigualdad":
                                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                                            $solicitud = $solicitud->fetchAll();

                                            if (empty($solicitud)) {
                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            } else {
                                                $cantidad = 0;
                                                foreach ($solicitud as $dinosaurioExiste) {
                                                    $cantidad += $dinosaurioExiste["cantidad"];
                                                    if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                                        break;
                                                    }
                                                }

                                                if ($cantidad > 6) {
                                                    break;
                                                }

                                               $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            }
                                            break;
                                        case "soledad":
                                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                                            $solicitud = $solicitud->fetchAll();

                                            if (empty($solicitud)) {
                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            } else {
                                                $cantidad = 0;
                                                foreach ($solicitud as $dinosaurioExiste) {
                                                    $cantidad += $dinosaurioExiste["cantidad"];
                                                }

                                                if ($cantidad > 1) {
                                                    break;
                                                }

                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            }
                                            break;
                                        case $recinto == "romance":
                                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                                            $solicitud = $solicitud->fetchAll();

                                            if (empty($solicitud)) {
                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            } else {
                                                $cantidad = 0;
                                                $dinosaurios = 0;
                                                foreach ($solicitud as $dinosaurioExiste) {
                                                    if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                                        $dinosaurios = $dinosaurioExiste["cantidad"];
                                                    }
                                                    $cantidad += $dinosaurioExiste["cantidad"];
                                                }

                                                if ($cantidad > 6) {
                                                    break;
                                                } else {
                                                    $dinosaurios++;
                                                }

                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            }
                                            break;
                                        case "monarquia":
                                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                                            $solicitud = $solicitud->fetch();

                                            if ($solicitud === false) {
                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            } else {
                                                break;
                                            }
                                            break;
                                        case "tres":
                                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                                            $solicitud = $solicitud->fetchAll();

                                            if (empty($solicitud)) {
                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            } else {
                                                $cantidad = 0;
                                                $dinosaurios = 0;
                                                foreach ($solicitud as $dinosaurioExiste) {
                                                    if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                                        $dinosaurios = $dinosaurioExiste["cantidad"];
                                                    }
                                                    $cantidad += $dinosaurioExiste["cantidad"];
                                                }

                                                if ($cantidad > 3) {
                                                    break;
                                                } else {
                                                    $dinosaurios++;
                                                }

                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            }
                                            break;
                                        case "rio":
                                            $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                                            $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                                            $solicitud = $solicitud->fetchAll();

                                            if (empty($solicitud)) {
                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            } else {
                                                $cantidad = 0;
                                                $dinosaurios = 0;
                                                foreach ($solicitud as $dinosaurioExiste) {
                                                    if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                                                        $dinosaurios = $dinosaurioExiste["cantidad"];
                                                    }
                                                    $cantidad += $dinosaurioExiste["cantidad"];
                                                }

                                                if ($cantidad > 6) {
                                                    break;
                                                } else {
                                                    $dinosaurios++;
                                                }

                                                $jugadasPermitidas[] = ["recinto" => $recinto, "dinosaurio" => $dinosaurio];
                                            }
                                            break;
                                    }
                                }
                            }

                            $jugadaElegida = $jugadasPermitidas[array_rand($jugadasPermitidas)];
                            $accion = $this->colocarDinosaurioNull($null, $jugadaElegida["dinosaurio"], $jugadaElegida["recinto"], $dadoId);
                            
                            return $accion;
                        }

                        return [
                            "state" => "success"
                        ];
                    } else {
                        return [
                            "state" => "notFound",
                            "ErrMessage" => "No se pudo obtener la partida por el id brindado"
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

    public function cambiarInventarios(string $token): array {
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
                        $inventarios = [];
                        for ($i = 0; $i < count($usuarios); $i++) {
                            $solicitud = $this->pdo->prepare("select i.* from inventario i join juega j on i.username = j.username where j.partidaId = :partidaId and j.numJugador = :numJugador;");
                            $solicitud->execute(["partidaId" => $partidaId, "numJugador" => $i + 1]);
                            $solicitud = $solicitud->fetchAll();

                            if (!empty($solicitud)) {
                                $inventarios[] = $solicitud;
                            } else {
                                return [
                                    "state" => "notFound",
                                    "ErrMessage" => "No se pudo obtener el inventario solicitado"
                                ];
                            }
                        }

                        $numInventarios = count($inventarios);
                        $nuevoInventarios = [];
                        for ($i = 0; $i < $numInventarios; $i++) {
                            $indiceInventarioOrigen = ($i - 1 + $numInventarios) % $numInventarios;
                            $inventarioRotado = $inventarios[$indiceInventarioOrigen];
                            $usernameDestino = $inventarios[$i][0]["username"];

                            for ($j = 0; $j < count($inventarioRotado); $j++) {
                                $inventarioRotado[$j]["username"] = $usernameDestino;
                            }
                            
                            $nuevoInventarios[] = $inventarioRotado;
                        }

                        foreach ($nuevoInventarios as $inventario) {
                            foreach ($inventario as $slot) {
                                $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where partidaId = :partidaId and username = :username and dinosaurioId = :dinosaurioId;");
                                $actualizar->execute(["username" => $slot["username"], "partidaId" => $partidaId, "dinosaurioId" => $slot["dinosaurioId"], "cantidad" => $slot["cantidad"]]);
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

    public function getDado(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select p.dadoId from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $solicitud = $solicitud->fetch();

            if ($solicitud !== false) {
                $dado = $solicitud["dadoId"];

                return [
                    "state" => "success",
                    "result" => $dado
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

    public function tirarDado(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select p.id, p.turnoActual, j.numJugador from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $solicitud = $solicitud->fetch();

            if ($solicitud !== false) {
                $partidaId = $solicitud["id"];
                $turnoActual = $solicitud["turnoActual"];
                $numJugador = $solicitud["numJugador"];

                if ($turnoActual == $numJugador) {
                    $solicitud = $this->pdo->query("select id from dado;");
                    $dados = $solicitud->fetchAll();

                    if (!empty($dados)) {
                        $dadoId = $dados[array_rand($dados)]["id"];
                        $actualizar = $this->pdo->prepare("update partida set dadoId = :dadoId where id = :id;");
                        $actualizar->execute(["dadoId" => $dadoId, "id" => $partidaId]);

                        return [
                            "state" => "success"
                        ];
                    } else {
                        return [
                            "state" => "notFound",
                            "ErrMessage" => "No se encuentran registradas las caras del dado en la base de datos"
                        ];
                    }
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

    // PARA LA SEGUNDA ENTREGA
    public function tirarDadoNulls($token): array {
        $credentials = $this->getUserCredentials($token);

        if ($credentials["state"] != "success") {
            return $credentials;
        }

        $username = $credentials["result"]["username"];
        $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        $partidaId = $solicitud->fetch();

        if ($partidaId !== false) {
            $partidaId = $partidaId["partidaId"];
            $isHost = $this->verificarHost($partidaId, $token);

            if ($isHost["state"] == "success") {
                $nulls = ["null", "null2", "null3", "null4"];
                foreach ($nulls as $username) {
                    $solicitud = $this->pdo->prepare("select p.id, p.turnoActual, j.numJugador from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
                    $solicitud->execute(["username" => $username]);
                    $solicitud = $solicitud->fetch();

                    if ($solicitud !== false) {
                        $partidaId = $solicitud["id"];
                        $turnoActual = $solicitud["turnoActual"];
                        $numJugador = $solicitud["numJugador"];

                        if ($turnoActual == $numJugador) {
                            $solicitud = $this->pdo->query("select id from dado;");
                            $dados = $solicitud->fetchAll();

                            if (!empty($dados)) {
                                $dadoId = $dados[array_rand($dados)]["id"];
                                $actualizar = $this->pdo->prepare("update partida set dadoId = :dadoId where id = :id;");
                                $actualizar->execute(["dadoId" => $dadoId, "id" => $partidaId]);

                                return [
                                    "state" => "success"
                                ];
                            } else {
                                return [
                                    "state" => "notFound",
                                    "ErrMessage" => "No se encuentran registradas las caras del dado en la base de datos"
                                ];
                            }
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
    }

    public function isTurnoTirarDado(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $solicitud = $this->pdo->prepare("select p.turnoActual, j.numJugador from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $solicitud = $solicitud->fetch();

            if ($solicitud !== false) {
                $turnoActual = $solicitud["turnoActual"];
                $numJugador = $solicitud["numJugador"];

                if ($turnoActual == $numJugador) { 
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
                    "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
                ];
            }
        } else {
            return $credentials;
        }
    }

    // PARA LA SEGUNDA ENTREGA (por culpa de los null de nuevo)
    public function isTurnoTirarDadoNull(string $username): array {
        if ($username == "null" || $username == "null2" || $username == "null3" || $username == "null4") {
            $solicitud = $this->pdo->prepare("select p.turnoActual, j.numJugador from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
            $solicitud->execute(["username" => $username]);
            $solicitud = $solicitud->fetch();

            if ($solicitud !== false) {
                $turnoActual = $solicitud["turnoActual"];
                $numJugador = $solicitud["numJugador"];

                if ($turnoActual == $numJugador) { 
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
                    "ErrMessage" => "El usuario brindado no esta registrado en ninguna partida"
                ];
            }
        } else {
            return [
                "state" => "nofound",
                "ErrMessage" => "El usuario null brindado no es valido"
            ];
        }
    }

    

    public function contarPuntosTablero(array $tablero): array {
        $trexRecinto = [];
        $tres = 0;
        $desiguales = 0;
        $rey = "";
        $reyCounter = 0;
        $solitario = "";
        $solitarioCounter = 0;
        $parejas = 0;
        $puntos = 0;

        foreach ($tablero as $recinto) {
            if ($recinto["recinto"] == "soledad") {
                $solitario = $recinto["dinosaurioId"];
                $solitarioCounter++;
            }

            if ($recinto["recinto"] == "monarquia") {
                $rey = $recinto["dinosaurioId"];
                $reyCounter++;
                $puntos += 7;
            }
        }

        foreach ($tablero as $recinto) {
            $recintoId = $recinto["recinto"];
            $dinosaurioId = $recinto["dinosaurioId"];
            $cantidad = $recinto["cantidad"];

            if (!isset($trexRecinto[$recintoId]) && $dinosaurioId == "rojo") {
                $trexRecinto[$recintoId] = true;
            }

            if ($dinosaurioId == $solitario) {
                $solitarioCounter++;
            }

            if ($dinosaurioId == $rey) {
                $reyCounter++;
            }

            switch (true) {
                case $recintoId == "rio":
                    $puntos += intval($cantidad);
                    break;
                case $recintoId == "igualdad":
                    switch ($cantidad) {
                        case 1:
                            $puntos += 2;
                            break;
                        case 2:
                            $puntos += 4;
                            break;
                        case 3:
                            $puntos += 8;
                            break;
                        case 4:
                            $puntos += 12;
                            break;
                        case 5:
                            $puntos += 18;
                            break;
                        case 6:
                            $puntos += 24;
                            break;
                    }
                    break;
                case $recintoId == "desigualdad":
                    $desiguales++;
                    switch ($desiguales) {
                        case 1:
                            $puntos += 1;
                            break;
                        case 2:
                            $puntos += 2;
                            break;
                        case 3:
                            $puntos += 3;
                            break;
                        case 4:
                            $puntos += 4;
                            break;
                        case 5:
                            $puntos += 5;
                            break;
                        case 6:
                            $puntos += 6;
                            break;
                    }
                    break;
                case $recintoId == "tres":
                    $tres += intval($cantidad);
                    if ($tres == 3) {
                        $puntos += 7;
                    }
                    break;
                case $recintoId == "romance":
                    $parejas += floor(intval($cantidad) / 2);
                    break;
            }
        }

        if ($solitarioCounter == 1) {
            $puntos += 7;
        }

        $puntos += count($trexRecinto) + $parejas * 5;

        return [
            "username" => $tablero[0]["username"],
            "puntos" => $puntos,
            "rey" => $rey,
            "reyCounter" => $reyCounter
        ];
    }

    public function contarDinosauriosTablero(array $tablero, string $dinosaurioId): int {
        $contador = 0;
        foreach ($tablero as $recinto) {
            if ($recinto["dinosaurioId"] == $dinosaurioId) {
                $contador++;
            }
        }

        return $contador;
    }

    

    // PARA LA SEGUNDA ENTREGA
    public function empezarTurnoNulls($token): array {
        $credentials = $this->getUserCredentials($token);

        if ($credentials["state"] != "success") {
            return $credentials;
        }

        $username = $credentials["result"]["username"];
        $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        $partidaId = $solicitud->fetch();

        if ($partidaId !== false) {
            $partidaId = $partidaId["partidaId"];
            $isHost = $this->verificarHost($partidaId, $token);

            if ($isHost["state"] == "success") {
                $nulls = ["null", "null2", "null3", "null4"];
                foreach ($nulls as $username) {
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
    }

    // PARA LA SEGUNDA ENTREGA
    public function terminarTurnoNulls(string $token): array {
        $nulls = ["null", "null2", "null3", "null4"];
        foreach ($nulls as $username) {
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
        }

        return [
            "state" => "success"
        ];
    }

    
}