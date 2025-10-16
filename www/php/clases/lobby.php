<?php
class Lobby extends GBloomDB {

    private Partida $partida;
    private GError $filtroLobbyLleno;

    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    
        $this->partida = new Partida();

        $this->filtroLobbyLleno = new GError(
            "Lobby Lleno",
            GError::forbidden,
            GError::exclusive,
            [
                "Lobby lleno" => function($input) {
                    $usuarios = $this->getLobbyUsuarios($input['codigo']);
                    return count($usuarios) >= $input['capacidad'];
                }
            ],
            "Validación Capacidad Lobby",
            true,
            "No es posible conectarse debido a que la sala se encuentra llena en el momento"
        );
    }

    public function verificarCodigo($codigo): array {
        $solicitud = $this->pdo->prepare("select codigo from lobby where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        
        $this->notFound->setOrigin("Lobby->verificarCodigo()");
        $this->notFound->setErrMessage("El codigo de acceso no se encuentra registrado en la base de datos");
        $this->notFound->filter($solicitud->fetch());
        
        return $this->returnSuccess(null);
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
        $host = $credentials["result"]["username"];

        $codigo = $this->generarCodigoAcceso();

        $solicitudPartida = $this->partida->getUltimaPartida($nombre, $host);
        $partida = $solicitudPartida["result"];
        $cantidadJugadores = $partida["cantidadJugadores"];
        $partidaId = $partida["id"];

        $solicitud = $this->pdo->prepare("select partidaId from lobby where partidaId = :id;");
        $solicitud->execute(["id" => $partidaId]);
        
        $this->notFound->setOrigin("Lobby->crearLobby()");
        $this->notFound->setFilterType(GError::exclusive);
        $this->notFound->setErrMessage("Un lobby ya existe para esta partida");
        $this->notFound->filter($solicitud->fetch());

        $insertar = $this->pdo->prepare("insert into lobby(codigo, partidaId, cantidadJugadores) values (:codigo, :partidaId, :cantidad);");
        $insertar->execute(["codigo" => $codigo, "cantidad" => $cantidadJugadores, "partidaId" => $partidaId]);

        return $this->returnSuccess($codigo);
    }

    public function conectaLobby(string $token, string $codigo): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];

        $eliminar = $this->pdo->prepare("delete from conecta where username = :username");
        $eliminar->execute(["username" => $username]);

        $solicitud = $this->pdo->prepare("select cantidadJugadores from lobby where codigo = :codigo");
        $solicitud->execute(["codigo" => $codigo]);
        
        $this->notFound->setOrigin("Lobby->conectaLobby()");
        $this->notFound->setErrMessage("El codigo brindado no es valido");
        $lobby = $this->notFound->filter($solicitud->fetch());

        $this->filtroLobbyLleno->filter([
            'codigo' => $codigo,
            'capacidad' => $lobby["cantidadJugadores"]
        ]);

        $insertar = $this->pdo->prepare("insert into conecta(codigo, username) values (:codigo, :username);");
        $insertar->execute(["codigo" => $codigo, "username" => $username]);
        
        return $this->returnSuccess(null);
    }

    public function desconectaLobby(string $token, string $codigo): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];

        $solicitud = $this->pdo->prepare("select codigo from lobby where codigo = :codigo");
        $solicitud->execute(["codigo" => $codigo]);
        
        $this->notFound->setOrigin("Lobby->desconectaLobby()");
        $this->notFound->setErrMessage("El codigo brindado no es valido");
        $this->notFound->filter($solicitud->fetch());

        $eliminar = $this->pdo->prepare("delete from conecta where username = :username and codigo = :codigo");
        $eliminar->execute(["username" => $username, "codigo" => $codigo]);
        
        return $this->returnSuccess(null);
    }

    public function getLobbyUsuarios(string $codigo): array {
        $solicitud = $this->pdo->prepare("select username from conecta where codigo = :codigo;");
        $solicitud->execute(["codigo" => $codigo]);
        $usuarios = $solicitud->fetchAll();
        
        return $this->returnSuccess($usuarios);
    }

    public function partidaComienza(string $codigo): bool {
        $this->notFound->setOrigin("Lobby->partidaComienza()");
        $this->notFound->setErrMessage("El codigo brindado no es valido");
        $solicitud = $this->pdo->prepare("select comienza from lobby where codigo = :codigo");
        $solicitud->execute(["codigo" => $codigo]);
        $comienza = $this->notFound->filter($solicitud->fetch())["comienza"];

        return $comienza === 1;
    }

    public function getLobbyNombre(string $codigo): array {
        $this->notFound->setOrigin("Lobby->getLobbyName()");
        $this->notFound->setErrMessage("El codigo brindado no es valido");
        $solicitud = $this->pdo->prepare("select p.nombre from partida p join lobby l where l.codigo = :codigo and l.partidaId = p.id");
        $solicitud->execute(["codigo" => $codigo]);
        $nombre = $this->notFound->filter($solicitud->fetch())["nombre"];

        return $this->returnSuccess($nombre);
    }

    public function getLobbyHost(string $codigo): array {
        $this->notFound->setOrigin("Lobby->getLobbyHost()");
        $this->notFound->setErrMessage("El codigo brindado no es valido");
        $solicitud = $this->pdo->prepare("select p.host from partida p join lobby l where l.codigo = :codigo and l.partidaId = p.id");
        $solicitud->execute(["codigo" => $codigo]);
        $host = $this->notFound->filter($solicitud->fetch())["host"];

        return $this->returnSuccess($host);
    }

    public function getCantidadMaxima(string $codigo): array {
        $this->notFound->setOrigin("Lobby->getCatidadMaxima()");
        $this->notFound->setErrMessage("El codigo brindado no es valido");
        $solicitud = $this->pdo->prepare("select p.cantidadJugadores from partida p join lobby l where l.codigo = :codigo and l.partidaId = p.id");
        $solicitud->execute(["codigo" => $codigo]);
        $cantidad = $this->notFound->filter($solicitud->fetch())["cantidadJugadores"];

        return $this->returnSuccess($cantidad);
    }

    public function eliminarLobby(string $codigo): array {
        $eliminar = $this->pdo->prepare("delete from conecta where codigo = :codigo");
        $eliminar->execute(["codigo" => $codigo]);
        $eliminar = $this->pdo->prepare("delete from lobby where codigo = :codigo");
        $eliminar->execute(["codigo" => $codigo]);

        return $this->returnSuccess(null);
    }
}