<?php
class Tablero extends Partida {

    private GError $filtroDinosaurioValido;
    private GError $filtroRecintoValido;
    private GError $filtroDadoValido;
    private Dado $dado;

    public function __construct() {
        parent::__construct();

        $this->filtroDinosaurioValido = new GError(
            "Dinosaurio Válido",
            GError::forbidden,
            GError::inclusive,
            [
                "dinosaurio_valido" => fn($input) => in_array($input, ["amarillo", "azul", "rojo", "morado", "verde", "naranja"])
            ],
            "Validación Dinosaurio",
            true,
            "El id de dinosaurio brindado es invalido"
        );

        $this->filtroRecintoValido = new GError(
            "Recinto Válido",
            GError::forbidden,
            GError::inclusive,
            [
                "recinto_valido" => fn($input) => in_array($input, ["igualdad", "desigualdad", "tres", "rio", "soledad", "monarquia", "romance"])
            ],
            "Validación Recinto", 
            true,
            "El recinto brindado es invalido"
        );

        $this->filtroDadoValido = new GError(
            "Dado Valido",
            GError::forbidden,
            GError::inclusive,
            [
                "cafeteria",
                "baños",
                "bosque",
                "desierto",
                "notrex",
                "vacio"
            ],
            "Validación de Dado",
            true,
            "El dado ingresado no es valido"
        );

        $this->dado = new Dado();
    }

    public function getTablero(string $token): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];
        $partidaId = $this->getPartidaJugando($token)["result"];

        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad, recinto from tablero where partidaId = :partidaId and username = :username;");
        $solicitud->execute(["partidaId" => $partidaId, "username" => $username]);
        $tablero = $solicitud->fetchAll();

        $this->notFound->setOrigin("Tablero->getTablero()");
        $this->notFound->setErrMessage("El tablero de juego se encuentra actualmente vacio");
        $this->notFound->filter($tablero);

        return $this->returnSuccess($tablero);
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
    public function colocarDinosaurio(string $token, string $dinosaurio, string $recinto, string $dadoId): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];
        $partidaId = $this->getPartidaJugando($token)["result"];
        
        $this->notFound->setOrigin("Tablero->colocarDinosaurio()");
        $this->notFound->setErrMessage("El jugador no posee el dinosaurio en su inventario para realizar la accion solicitada");
        $solicitud = $this->pdo->prepare("select cantidad from inventario where partidaId = :partidaId and username = :username and dinosaurioId = :dinosaurioId;");
        $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]);
        $solicitud = $this->notFound->filter($solicitud->fetch());

        $cantidadDisponibles = $solicitud["cantidad"];

        $cafeteria = false;
        $baños = false;
        $bosque = false;
        $desierto = false;

        $this->filtroDinosaurioValido->filter($dinosaurio);
        $this->filtroRecintoValido->filter($recinto);
        $this->filtroDadoValido->filter($dadoId);

        $tiroDado = $this->dado->isTurnoTirarDado($token)["result"];

        if (!$tiroDado) {
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

        $recintosProhibidos = [];

        try {
            $tablero = $this->getTablero($token);

            foreach ($tablero["result"] as $tableroRecinto) {
                if ($tableroRecinto["dinosaurioId"] == "rojo" && $dadoId == "notrex" && !isset($recintosProhibidos[$tableroRecinto["recinto"]])) {
                    $recintosProhibidos[$tableroRecinto["recinto"]] = true;
                }

                if ($dadoId == "vacio" && !isset($recintosProhibidos[$tableroRecinto["recinto"]])) {
                    $recintosProhibidos[$tableroRecinto["recinto"]] = true;
                }
            }

            $this->notFound->setOrigin("Tablero->colocarDinosaurio()");
            $this->notFound->setAutoThrow(false);
            $this->notFound->setErrMessage("No puede colocar el dinosaurio porque un trex ocupa este recinto");
            $this->notFound->filter(!isset($recintosProhibidos[$recinto]));
        } catch (Exception $e) {

        }

        $this->notFound->throw();
        $this->notFound->setAutoThrow(true);

        switch (true) {
            case ($recinto == "igualdad") && ($bosque || $cafeteria):
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                $solicitud = $solicitud->fetch();
                

                if ($solicitud === false) {
                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);

                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
                } else {
                    $cantidad = $solicitud["cantidad"];

                    if ($cantidad > 6) {
                        return [
                            "status" => GError::forbidden,
                            "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                        ];
                    } else {
                        $cantidad++;
                    }

                    if ($solicitud["dinosaurioId"] == $dinosaurio) {
                        $actualizar = $this->pdo->prepare("update tablero set cantidad = :cantidad where dinosaurioId = :dinosaurioId and recinto = :recinto and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto, "cantidad" => $recinto]);
                        
                        if ($cantidadDisponibles >= 1) {
                            
                            $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId username = :username and partidaId = :partidaId;");
                            $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                        } else {
                            
                            $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                            $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                        }

                        return $this->returnSuccess(null);
                    } else {
                        return [
                            "status" => GError::forbidden,
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

                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
                } else {
                    $cantidad = 0;
                    foreach ($solicitud as $dinosaurioExiste) {
                        $cantidad += $dinosaurioExiste["cantidad"];
                        if ($dinosaurioExiste["dinosaurioId"] == $dinosaurio) {
                            return [
                                "status" => GError::forbidden,
                                "ErrMessage" => "El recinto solo admite un dinosaurio de cada especie"
                            ];
                        }
                    }

                    if ($cantidad > 6) {
                        return [
                            "status" => GError::forbidden,
                            "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                        ];
                    }

                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                    
                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
                }
            case ($recinto == "soledad") && ($desierto || $baños):
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                $solicitud = $solicitud->fetchAll();

                if (empty($solicitud)) {
                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                    
                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
                } else {
                    $cantidad = 0;
                    foreach ($solicitud as $dinosaurioExiste) {
                        $cantidad += $dinosaurioExiste["cantidad"];
                    }

                    if ($cantidad > 1) {
                        return [
                            "status" => GError::forbidden,
                            "ErrMessage" => "El recinto donde se esta tratando de colocar un dinosaurio se encuentra lleno"
                        ];
                    }

                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                    
                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
                }
            case ($recinto == "romance") && ($desierto || $cafeteria):
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                $solicitud = $solicitud->fetchAll();

                if (empty($solicitud)) {
                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                    
                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
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
                            "status" => GError::forbidden,
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

                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }
                    
                    return $this->returnSuccess(null);
                }
            case ($recinto == "monarquia") && ($baños || $bosque):
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                $solicitud = $solicitud->fetch();

                if ($solicitud === false) {
                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                    
                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
                } else {
                    return [
                        "status" => GError::forbidden,
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
                    
                    return $this->returnSuccess(null);
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
                            "status" => GError::forbidden,
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
                    
                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
                }
            case "rio":
                $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from tablero where partidaId = :partidaId and username = :username and recinto = :recinto;");
                $solicitud->execute(["username" => $username, "partidaId" => $partidaId, "recinto" => $recinto]);
                $solicitud = $solicitud->fetchAll();

                if (empty($solicitud)) {
                    $insertar = $this->pdo->prepare("insert into tablero(username, partidaId, dinosaurioId, recinto, cantidad) values (:username, :partidaId, :dinosaurioId, :recinto, 1);");
                    $insertar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "recinto" => $recinto]);
                    
                    return $this->returnSuccess(null);
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
                            "status" => GError::forbidden,
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
                    
                    
                    if ($cantidadDisponibles >= 1) {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = :cantidad where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio, "cantidad" => $cantidadDisponibles-1]);
                    } else {
                        
                        $actualizar = $this->pdo->prepare("update inventario set cantidad = 0 where dinosaurioId = :dinosaurioId and username = :username and partidaId = :partidaId;");
                        $actualizar->execute(["username" => $username, "partidaId" => $partidaId, "dinosaurioId" => $dinosaurio]); 
                    }

                    return $this->returnSuccess(null);
                }
            default:
                return [
                    "status" => GError::forbidden,
                    "ErrMessage" => "No se ha podido colocar el dinosaurio brindado debido a las reestricciones del dadoo de colocacio"
                ];
        }
    
    }
}