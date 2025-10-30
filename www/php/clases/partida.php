<?php
class Partida extends GBloomDB {

    private GError $validacionPartida;

    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    
        $this->validacionPartida = new GError(
            "Validación Partida",
            GError::forbidden,
            GError::all_match,
            [
                "nombre longitud" => fn($input) => strlen($input["nombre"]) <= 32,
                "cantidad jugadores" => fn($input) => $input["cantidadJugadores"] >= 2 && $input["cantidadJugadores"] <= 5,
                "modo juego" => fn($input) => $input["modoJuego"] == "virtual" || $input["modoJuego"] == "seguimiento"
            ],
            "Partida->crearPartida()",
            true,
            "Datos de partida inválidos"
        );

    }

    public function verificarHost(int $partidaId, string $token): array {
        $usuario = $this->getUserCredentials($token)["result"]["username"];
        
        $solicitud = $this->pdo->prepare("select host from partida where id = :id;");
        $solicitud->execute(["id" => $partidaId]);
        
        $this->notFound->setOrigin("Perfil->verificarHost()");
        $this->notFound->setErrMessage("El id de partida indexada no existe");
        $host = $this->notFound->filter($solicitud->fetch())["host"];
        
        $this->notFound->setErrMessage("El host de la partida no coincide con el usuario brindado");
        $this->notFound->setFilterType(GError::inclusive);
        $this->notFound->filter($host === $usuario);
        
        return $this->returnSuccess(null);
    }

    public function getUltimaPartida(string $nombre, string $host): array {
        $solicitud = $this->pdo->prepare("select id, fecha, horaInicio, cantidadJugadores from partida where nombre = :nombre and host = :host order by fecha desc limit 1;");
        $solicitud->execute(["nombre" => $nombre, "host" => $host]);
        $partida = $solicitud->fetch();

        $this->notFound->setOrigin("Partida->getUltimaPartida()");
        $this->notFound->setErrMessage("No se ha encontrado ninguna partida con el nombre ni host brindado");
        $this->notFound->filter($partida);
        
        return $this->returnSuccess($partida);
    }

    public function crearPartida(string $nombre, string $token, int $cantidadJugadores, string $modoJuego): array {
        try {
            $this->terminarPartidaActiva($token);
        } catch (Exception $e) {

        }

        $credentials = $this->getUserCredentials($token);
        $host = $credentials["result"]["username"];
        $this->validacionPartida->filter(["nombre" => $nombre, "cantidadJugadores" => $cantidadJugadores, "modoJuego" => $modoJuego]);

        $insertar = $this->pdo->prepare("insert into partida(host, nombre, fecha, cantidadJugadores, modoJuego, turnoActual, faseActual) values (:host, :nombre, NOW(), :cantidad, :modo, 1, 1);");
        $insertar->execute(["host" => $host, "nombre" => $nombre, "cantidad" => $cantidadJugadores, "modo" => $modoJuego]);
        
        $partida = $this->getUltimaPartida($nombre, $host);
        
        return $this->returnSuccess($partida["result"]);
    }

    public function eliminarPartida(string $nombre, string $token): array {
        $credentials = $this->getUserCredentials($token);
        
        $host = $credentials["result"]["username"];
        $partida = $this->getUltimaPartida($nombre, $host);
        $partidaId = $partida["result"]["id"];
        
        $solicitud = $this->pdo->prepare("select codigo from lobby where partidaId = :id;");
        $solicitud->execute(["id" => $partidaId]);
        $codigo = $solicitud->fetch();
        
        if ($codigo !== false) {
            $codigo = $codigo["codigo"];
            $this->pdo->prepare("delete from conecta where codigo = :codigo")->execute(["codigo" => $codigo]);
            $this->pdo->prepare("delete from lobby where codigo = :codigo")->execute(["codigo" => $codigo]);
        }
        
        $this->pdo->prepare("delete from inventario where partidaId = :id")->execute(["id" => $partidaId]);
        $this->pdo->prepare("delete from tablero where partidaId = :id")->execute(["id" => $partidaId]);
        $this->pdo->prepare("delete from juega where partidaId = :id")->execute(["id" => $partidaId]);
        $this->pdo->prepare("delete from partida where id = :id")->execute(["id" => $partidaId]);
        
        return $this->returnSuccess(null);
    }

    public function conectaPartida(string $codigo): array {
        $solicitud = $this->pdo->prepare("select username from conecta where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $usuarios = $solicitud->fetchAll();
        
        $this->notFound->setOrigin("Partida->conectaPartida()");
        $this->notFound->setErrMessage("No se han encontrado usuarios conectados actualmente al lobby indicado");
        $this->notFound->filter(!empty($usuarios));
        
        $solicitud = $this->pdo->prepare("select partidaId from lobby where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $partidaId = $solicitud->fetch();
        
        $this->notFound->setErrMessage("No se ha podido encontrar el lobby utilizando el codigo de accesso brindado");
        $this->notFound->filter($partidaId);
        
        $partidaId = $partidaId["partidaId"];
        
        foreach ($usuarios as $usuario) {
            $username = $usuario["username"];
            $this->pdo->prepare("update juega set jugando = 0 where username = :username;")->execute(["username" => $username]);
            $this->pdo->prepare("update usuario set jugadas = jugadas + 1 where username = :username;")->execute(["username" => $username]);
            $this->pdo->prepare("insert into juega(username, partidaId, jugando) values (:username, :partidaId, 1);")
                    ->execute(["username" => $username, "partidaId" => $partidaId]);
        }
        
        return $this->returnSuccess(null);
    }

    public function iniciarPartida(string $token, string $codigo): array {
        $solicitud = $this->pdo->prepare("select partidaId from lobby where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $partidaId = $solicitud->fetch();
        
        $this->notFound->setOrigin("Partida->iniciarPartida()");
        $this->notFound->setErrMessage("No se ha podido encontrar el lobby utilizando el codigo de accesso brindado");
        $this->notFound->filter($partidaId);
        
        $partidaId = $partidaId["partidaId"];
        $isHost = $this->verificarHost($partidaId, $token);
        
        $this->pdo->prepare("update partida set horaInicio = CURTIME() where id = :id;")->execute(["id" => $partidaId]);
        $this->pdo->prepare("update lobby set comienza = 1 where codigo = :codigo;")->execute(["codigo" => $codigo]);
        
        $accion = $this->conectaPartida($codigo);
        
        return $this->returnSuccess(null);
    }

    public function terminarPartidaActiva(string $token): array {
        $credentials = $this->getUserCredentials($token);
        
        $host = $credentials["result"]["username"];
        $solicitud = $this->pdo->prepare("select id from partida where horaFinal is null and host = :host order by fecha desc limit 1;");
        $solicitud->execute(["host" => $host]);
        $partida = $solicitud->fetch();
        
        $this->notFound->setErrMessage("El usuario brindado no hostea ninguna partida activa");
        $this->notFound->filter($partida);
        
        $this->pdo->prepare("update partida set horaFinal = CURTIME() where id = :id")->execute(["id" => $partida["id"]]);
        
        return $this->returnSuccess(null);
    }

    public function setupOrdenJugadores(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        $isHost = $this->verificarHost($partidaId, $token);
        
        $solicitud = $this->pdo->prepare("
            select usuario.fechaNacimiento, juega.username from juega join usuario 
            on usuario.username = juega.username 
            where juega.partidaId = :partidaId 
            order by usuario.fechaNacimiento desc;"
        );
        $solicitud->execute(["partidaId" => $partidaId]);
        $usuarios = $solicitud->fetchAll();
        
        $this->notFound->setErrMessage("No hay usuarios conectados a la partida indexada");
        $this->notFound->filter($usuarios);
        
        foreach ($usuarios as $index => $usuario) {
            $this->pdo->prepare("update juega set numJugador = :orden where username = :username and jugando = 1;")
                    ->execute(["orden" => $index + 1, "username" => $usuario["username"]]);
        }
        
        return $this->returnSuccess(null);
    }

    public function isTurnoTerminado(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        
        $solicitud = $this->pdo->prepare("select estado from juega where partidaId = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        
        $this->notFound->setOrigin("Partida->isTurnoTerminado()");
        $this->notFound->setErrMessage("No se han encontrado jugadores dentro de la partida");
        $estados = $this->notFound->filter($solicitud->fetchAll());
        
        foreach ($estados as $estado) {
            if ($estado["estado"] == "jugandoTurno") {
                return $this->returnSuccess(false);
            }
        }
        
        return $this->returnSuccess(true);
    }

    public function pasarTurno(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        $this->verificarHost($partidaId, $token);
        
        $solicitud = $this->pdo->prepare("select turnoActual from partida where id = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        
        $this->notFound->setOrigin("Partida->pasarTurno()");
        $this->notFound->setErrMessage("No hay usuarios conectados a la partida indexada");
        $partida = $this->notFound->filter($solicitud->fetch());
        
        $turnoActual = intval($partida["turnoActual"]);
        $nuevoTurno = $turnoActual < 6 ? $turnoActual + 1 : 1;

        $actualizar = $this->pdo->prepare("update partida set turnoActual = :turnoActual where id = :partidaId");
        $actualizar->execute(["turnoActual" => $nuevoTurno, "partidaId" => $partidaId]);
        
        return $this->returnSuccess(null);
    }

    public function pasarFase(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        $this->verificarHost($partidaId, $token);
        
        $solicitud = $this->pdo->prepare("select faseActual from partida where id = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        
        $this->notFound->setOrigin("Partida->pasarFase()");
        $this->notFound->setErrMessage("No hay usuarios conectados a la partida indexada");
        $partida = $this->notFound->filter($solicitud->fetch());
        $faseActual = intval($partida["faseActual"]);
        
        if ($faseActual >= 2) {
            $ganadores = $this->getGanadores($token)["result"];
            
            foreach ($ganadores as $ganador) {
                $actualizar = $this->pdo->prepare("update usuario set victorias = victorias + 1 where username = :username");
                $actualizar->execute(["username" => $ganador["username"]]);
            }

            $this->terminarPartidaActiva($token);
        } else {     
            $nuevaFase = $faseActual + 1;
            
            $actualizar = $this->pdo->prepare("update partida set faseActual = :faseActual where id = :partidaId");
            $actualizar->execute(["faseActual" => $nuevaFase, "partidaId" => $partidaId]);
        }

        return $this->returnSuccess(null);
    }

    public function contarPuntosJugadores(string $token, Tablero $tablero): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        $this->verificarHost($partidaId, $token);
        
        $solicitud = $this->pdo->prepare("select username from juega where partidaId = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        
        $this->notFound->setOrigin("Partida->contarPuntosJugadores()");
        $this->notFound->setErrMessage("No hay usuarios conectados a la partida indexada");
        $usuarios = $this->notFound->filter($solicitud->fetchAll());
        
        $tablerosContados = [];
        
        foreach ($usuarios as $index => $usuario) {
            $solicitud = $this->pdo->prepare("select t.* from tablero t where t.partidaId = :partidaId and t.username = :username");
            $solicitud->execute(["partidaId" => $partidaId, "username" => $usuario["username"]]);
            
            $this->notFound->setErrMessage("No se pudo obtener el tablero solicitado");
            $contenido = $this->notFound->filter($solicitud->fetchAll());

            $tablerosContados[] = ["username" => $usuario["username"], "puntaje" => $tablero->contarPuntosTablero($contenido), "tablero" => $contenido];
        }
        
        foreach ($tablerosContados as $tableroContado) {
            foreach ($tablerosContados as $otroTablero) {
                if ($tablero->contarDinosauriosTablero($otroTablero["tablero"], $tableroContado["puntaje"]["rey"]) > $tableroContado["puntaje"]["reyCounter"]) {
                    $tableroContado["puntaje"]["puntos"] -= 7;
                }
            }
            
            $actualizar = $this->pdo->prepare("update juega set puntos = :puntos where username = :username and partidaId = :partidaId;");
            $actualizar->execute([
                "puntos" => $tableroContado["puntaje"]["puntos"], 
                "username" => $tableroContado["username"], 
                "partidaId" => $partidaId
            ]);
        }
        
        return $this->returnSuccess(null);
    }

    public function empezarTurno(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        
        $actualizar = $this->pdo->prepare("update juega set estado = :estado where partidaId = :partidaId;");
        $actualizar->execute([
            "estado" => "jugandoTurno", 
            "partidaId" => $partidaId
        ]);
        
        return $this->returnSuccess(null);
    }

    public function terminarTurno(string $token): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];
        
        $partidaId = $this->getPartidaJugando($token)["result"];
        
        $actualizar = $this->pdo->prepare("update juega set estado = :estado where username = :username and partidaId = :partidaId;");
        $actualizar->execute([
            "estado" => "turnoTerminado", 
            "partidaId" => $partidaId, 
            "username" => $username
        ]);
        
        return $this->returnSuccess(null);
    }

    public function getTurnoActual(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        $solicitud = $this->pdo->prepare("select turnoActual from partida where id = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        
        $this->notFound->setOrigin("Partida->getTurnoActual()");
        $this->notFound->setErrMessage("Error al buscar partida id");
        $partida = $this->notFound->filter($solicitud->fetch());
        
        return $this->returnSuccess(intval($partida["turnoActual"]));
    }

    public function getFaseActual(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        $this->notFound->setOrigin("Partida->getFaseActual");
        
        $solicitud = $this->pdo->prepare("select faseActual from partida where id = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        
        $this->notFound->setErrMessage("Error al buscar partida id");
        $partida = $this->notFound->filter($solicitud->fetch());
        
        return $this->returnSuccess(intval($partida["faseActual"]));
    }

    public function isPartidaOver(string $token): array {
        $credentials = $this->getUserCredentials($token);
        $partidaId = $this->getPartidaJugando($token)["result"];
        
        $solicitud = $this->pdo->prepare("select horaFinal from partida where id = :id;");
        $solicitud->execute(["id" => $partidaId]);
        $solicitud = $solicitud->fetch();

        $horaFinal = $solicitud["horaFinal"];

        if ($horaFinal !== NULL) {
            return $this->returnSuccess(true);
        } else {
            return $this->returnSuccess(false);
        }
    }

    public function getPartidaJugando(string $token): array {
        $credentials = $this->getUserCredentials($token);
        
        $username = $credentials["result"]["username"];
        $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        $partida = $solicitud->fetch();
        
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $this->notFound->filter($partida);
        
        $partidaId = $partida["partidaId"];
        return $this->returnSuccess($partidaId);
    }

    public function getJugadores(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];
        
        $solicitud = $this->pdo->prepare("select * from juega where partidaId = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        $jugadores = $solicitud->fetchAll();
        
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $this->notFound->filter($jugadores);
        
        return $this->returnSuccess($jugadores);
    }

    public function getGanadores(string $token): array {
        $partidaId = $this->getPartidaJugando($token)["result"];

        $solicitud = $this->pdo->prepare("select username, puntos from juega where partidaId = :partidaId and puntos = (select max(puntos) from juega where partidaId = :partidaId)");
        $solicitud->execute(["partidaId" => $partidaId]);
        $jugadores = $solicitud->fetchAll();
        
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $this->notFound->filter($jugadores);
        
        return $this->returnSuccess($jugadores);
    }

    public function getEstadoTurno(string $token): array {
        $username = $this->getUserCredentials($token)["result"]["username"];
        $partidaId = $this->getPartidaJugando($token)["result"];

        $solicitud = $this->pdo->prepare("select estado from juega where partidaId = :partidaId and username = :username");
        $solicitud->execute(["partidaId" => $partidaId, "username" => $username]);
        $estado = $solicitud->fetch()["estado"];
        
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $this->notFound->filter($estado);
        
        return $this->returnSuccess($estado);
    }
}