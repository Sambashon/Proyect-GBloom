<?php
include "partida.php";

class Inventario extends Partida {

    public function __construct() {
        parent::__construct();
    }

    public function crearInventarios(array $usuarios): array {
        $meeples = [10, 10, 10, 10, 10, 10];
        $jugadores = [];
        $colores = ["amarillo", "azul", "rojo", "morado", "verde", "naranja"];

        foreach ($usuarios as $usuario) {
            $jugador = array_fill(0, 6, 0);

            for ($j = 0; $j < 6; $j++) {
                $disponibles = array_keys(array_filter($meeples, fn($m) => $m > 0));
                
                if (empty($disponibles)) break;
                
                $meeple = $disponibles[array_rand($disponibles)];
                $jugador[$meeple]++;
                $meeples[$meeple]--;
            }

            $dinosaurios = [];
            foreach ($jugador as $index => $cantidad) {
                $dinosaurios[$colores[$index]] = $cantidad;
            }

            $jugadores[] = [
                "dinosaurios" => $dinosaurios,
                "username" => $usuario["username"]
            ];
        }

        return $jugadores;
    }

    public function setupInventarios(string $token): array {
        $username = $this->getUserCredentials($token)["result"]["username"];
        
        $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        
        $this->notFound->setOrigin("Draftosaurus->setupInventarios()");
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $partidaId = $this->notFound->filter($solicitud->fetch())["partidaId"];
        
        $this->verificarHost($partidaId, $token);
        
        $solicitud = $this->pdo->prepare("select username from juega where partidaId = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        $usuarios = $solicitud->fetchAll();
        
        $this->notFound->setErrMessage("No hay usuarios conectados a la partida indexada");
        $this->notFound->filter(!empty($usuarios));
        
        $inventarios = $this->crearInventarios($usuarios);
        foreach ($inventarios as $inventario) {
            foreach ($inventario["dinosaurios"] as $id => $cantidad) {
                $insertar = $this->pdo->prepare("insert into inventario(username, partidaId, dinosaurioId, cantidad) values (:username, :partidaId, :dinosaurioId, :cantidad);");
                $insertar->execute(["username" => $inventario["username"], "partidaId" => $partidaId, "dinosaurioId" => $id, "cantidad" => $cantidad]);
            }
        }
        
        return $this->returnSuccess(null);
    }

    public function getInventario(string $token): array {
        $username = $this->getUserCredentials($token)["result"]["username"];
        
        $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        
        $this->notFound->setOrigin("Draftosaurus->getInventario()");
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $partidaId = $this->notFound->filter($solicitud->fetch())["partidaId"];
        
        $solicitud = $this->pdo->prepare("select dinosaurioId, cantidad from inventario where partidaId = :partidaId and username = :username;");
        $solicitud->execute(["partidaId" => $partidaId, "username" => $username]);
        $inventario = $solicitud->fetchAll();
        
        $this->notFound->setErrMessage("El inventario de juego se encuentra actualmente vacio");
        $this->notFound->filter(!empty($inventario));
        
        return $this->returnSuccess($inventario);
    }

    public function cambiarInventarios(string $token): array {
        $username = $this->getUserCredentials($token)["result"]["username"];
        
        $solicitud = $this->pdo->prepare("select partidaId from juega where username = :username and jugando = 1;");
        $solicitud->execute(["username" => $username]);
        
        $this->notFound->setOrigin("Draftosaurus->cambiarInventarios()");
        $this->notFound->setErrMessage("El usuario brindado no esta registrado en ninguna partida");
        $partidaId = $this->notFound->filter($solicitud->fetch())["partidaId"];
        
        $this->verificarHost($partidaId, $token);
        
        $solicitud = $this->pdo->prepare("select username from juega where partidaId = :partidaId;");
        $solicitud->execute(["partidaId" => $partidaId]);
        $usuarios = $solicitud->fetchAll();
        
        $this->notFound->setErrMessage("No hay usuarios conectados a la partida indexada");
        $this->notFound->filter(!empty($usuarios));
        
        $inventarios = [];
        for ($i = 0; $i < count($usuarios); $i++) {
            $solicitud = $this->pdo->prepare("select i.* from inventario i join juega j on i.username = j.username where j.partidaId = :partidaId and j.numJugador = :numJugador;");
            $solicitud->execute(["partidaId" => $partidaId, "numJugador" => $i + 1]);
            $inventario = $solicitud->fetchAll();
            
            $this->notFound->setErrMessage("No se pudo obtener el inventario solicitado");
            $this->notFound->filter(!empty($inventario));
            
            $inventarios[] = $inventario;
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
        
        return $this->returnSuccess();
    }
}