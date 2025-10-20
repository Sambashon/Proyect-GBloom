<?php
class Dado extends Partida {

    public function __construct() {
        parent::__construct();
    }

    public function getDado(string $token): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];

        $this->notFound->setOrigin("Dado->getDado()");
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $solicitud = $this->pdo->prepare("select p.dadoId from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        $solicitud = $this->notFound->filter($solicitud->fetch());
        $dado = $solicitud["dadoId"];

        return $this->returnSuccess($dado);
    }

    public function tirarDado(string $token): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];

        $this->notFound->setOrigin("Dado->tirarDado()");
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $solicitud = $this->pdo->prepare("select p.id, p.turnoActual, j.numJugador from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        $partida = $this->notFound->filter($solicitud->fetch());

        $this->notFound->setErrMessage("El jugador brindado no le toca turno para tirar el dado");
        $this->notFound->filter($partida["turnoActual"] == $partida["numJugador"]);

        $this->notFound->setErrMessage("No se encuentran registradas las caras del dado en la base de datos");
        $solicitud = $this->pdo->query("select id from dado;");
        $dados = $this->notFound->filter($solicitud->fetchAll());

        $dadoId = $dados[array_rand($dados)]["id"];
        $actualizar = $this->pdo->prepare("update partida set dadoId = :dadoId where id = :id;");
        $actualizar->execute(["dadoId" => $dadoId, "id" => $partida["id"]]);

        return $this->returnSuccess(null);
    }

    public function setupDado(string $token): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];

        $this->notFound->setOrigin("Dado->tirarDado()");
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $solicitud = $this->pdo->prepare("select p.id, p.turnoActual, j.numJugador from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        $partida = $this->notFound->filter($solicitud->fetch());

        $this->notFound->setErrMessage("No se encuentran registradas las caras del dado en la base de datos");
        $solicitud = $this->pdo->query("select id from dado;");
        $dados = $this->notFound->filter($solicitud->fetchAll());

        $dadoId = $dados[array_rand($dados)]["id"];
        $actualizar = $this->pdo->prepare("update partida set dadoId = :dadoId where id = :id;");
        $actualizar->execute(["dadoId" => $dadoId, "id" => $partida["id"]]);

        return $this->returnSuccess(null);
    }

    public function isTurnoTirarDado(string $token): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];

        $this->notFound->setOrigin("Dado->isTurnoTirarDado()");
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $solicitud = $this->pdo->prepare("select p.turnoActual, j.numJugador from juega j join partida p on j.partidaId = p.id where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        $partida = $this->notFound->filter($solicitud->fetch());

        return $this->returnSuccess($partida["turnoActual"] == $partida["numJugador"]);
    }
}