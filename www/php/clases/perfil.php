<?php
include "gbloomdb.php";
class Perfil extends GBloomDB {

    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    }

    public function registrarUsuario(string $username, string $contraseña, string $correo, DateTime $fechaNacimiento): array {
        $solicitud = $this->pdo->prepare("select username from usuario where username = :username;");
        $solicitud->execute(["username" => $username]);
        $existe = $solicitud->fetch();

        // Un dia quisiera agregar reestriccion de edad para las cuentas CUIDADIIITO
        if ($existe === false) {
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => "El formato del correo electronico no es valido"
                ];
            }

            if (strlen($username) > 32) {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => "El nombre de usuario brindado es demasiado largo"
                ];
            }

            if (strpos($username, ' ') !== false) {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => "El nombre de usuario brindado no puede contener espacios"
                ];
            }

            if (strlen($contraseña) >= 8) {
                if (strlen($contraseña) <= 100) {
                    $contraseña = password_hash($contraseña, PASSWORD_DEFAULT);
                    $fechaNacimiento = $fechaNacimiento->format("Y-m-d");

                    $insertar = $this->pdo->prepare("insert into usuario(username, correo, contraseña, fechaNacimiento) values (:username, :correo, :contrasena, :fechaNacimiento);");
                    $insertar->execute(["username" => $username, "correo" => $correo, "contrasena" => $contraseña, "fechaNacimiento" => $fechaNacimiento]);

                    return [
                        "state" => "success"
                    ];
                } else {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => "La contraseña brindada es demasiado larga"
                    ];
                }
            } else {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => "La contraseña brindada debe contener como minimo 8 caracteres"
                ];
            }
        } else {
            return [
                "state" => "forbidden",
                "ErrMessage" => "El nombre de usuario no se puede registrar porque ya existe"
            ];
        }
    }

    public function generarToken(): string {
        do {
            $token = bin2hex(random_bytes(32));
            $solicitud = $this->pdo->prepare("select token from sesion where token = :token");
            $solicitud->execute(["token" => $token]);
            $existe = $solicitud->fetch() !== false;
        } while ($existe);

        return $token;
    }

    public function accederUsuario(string $username, string $contraseña, bool $mantener): array {
        $solicitud = $this->pdo->prepare("select username, contraseña from usuario where username = :username;");
        $solicitud->execute(["username" => $username]);
        $usuario = $solicitud->fetch();

        if ($usuario !== false) {
            if (password_verify($contraseña, $usuario["contraseña"])) {
                $token = $this->generarToken();
                $ahora = new DateTime();
                $ahora = $ahora->format("Y-m-d H:i:s");
                $expira = new DateTime();

                if ($mantener) {
                    $expiraFormat = $expira->modify("+1 months")->format("Y-m-d H:i:s");   
                } else {
                    $expiraFormat = $expira->modify("+1 days")->format("Y-m-d H:i:s");
                }

                $insertar = $this->pdo->prepare("insert into sesion(token, username, expira, fecha, mantener) values (:token, :username, :expira, :fecha, :mantener);");
                $insertar->execute(["username" => $username, "token" => $token, "expira" => $expiraFormat, "mantener" => $mantener, "fecha" => $ahora]);

                return [
                    "state" => "success",
                    "result" => ["token" => $token, "expira" => $expira]
                ];
            } else {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => "La contraseña o el usuario ingresados no son validos"
                ];
            }
        } else {
            return [
                "state" => "forbidden",
                "ErrMessage" => "La contraseña o el usuario ingresados no son validos"
            ];
        }
    }

    public function eliminarUsuario(string $token): array {
        $credentials = $this->getUserCredentials($token);
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];

            $eliminar = $this->pdo->prepare("delete from sesion where username = :username");
            $eliminar->execute(["username" => $username]);

            $eliminar = $this->pdo->prepare("delete from tablero where username = :username");
            $eliminar->execute(["username" => $username]);

            $eliminar = $this->pdo->prepare("delete from inventario where username = :username");
            $eliminar->execute(["username" => $username]);

            $eliminar = $this->pdo->prepare("delete from conecta where username = :username");
            $eliminar->execute(["username" => $username]);

            $eliminar = $this->pdo->prepare("delete from usuario where username = :username");
            $eliminar->execute(["username" => $username]);

            $actualizar = $this->pdo->prepare("update juega set username = deletedUser where username = :username");
            $actualizar->execute(["username" => $username]);

            $actualizar = $this->pdo->prepare("update partida set host = deletedUser where host = :username");
            $actualizar->execute(["username" => $username]);

            return [
                "state" => "success"
            ];
        } else {
            return $credentials;
        }
    }

    public function cerrarSesion(string $token): array {
        $eliminar = $this->pdo->prepare("delete from sesion where token = :token");
        $eliminar->execute(["token" => $token]);

        return [
            "state" => "success"
        ];
    }

    public function editarUsuario(string $token, string $username): array {
        return [
            "state" => "success"
        ];
    }
    
}