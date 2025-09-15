<?php
include "gbloomdb.php";
include "gbmailer.php";

class Perfil extends GBloomDB {

    public function __construct() {
        parent::__construct("localhost", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    }

    public function registrarUsuario(string $username, string $contraseña, string $correo): array {
        $solicitud = $this->pdo->prepare("select username from usuario where username = :username;");
        $solicitud->execute(["username" => $username]);
        $existe = $solicitud->fetch();

        // Un dia quisiera agregar reestriccion de edad para las cuentas CUIDADIIITO
        if ($existe === false) {
            $solicitud = $this->pdo->prepare("select correo from usuario where correo = :correo;");
            $solicitud->execute(["correo" => $correo]);
            $existe = $solicitud->fetch();

            if ($existe === false) {
                $errMessages = [];
                $error = false;

                if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $errMessages[] = "El formato del correo electronico no es valido";
                    $error = true;
                }
                if (strlen($username) > 32) {
                    $errMessages[] = "El nombre de usuario brindado es demasiado largo";
                    $error = true;
                }
                if (strpos($username, ' ') !== false) {
                    $errMessages[] = "El nombre de usuario brindado no puede contener espacios";
                    $error = true;
                }
                if (strlen($contraseña) < 8) {
                    $error = true;
                    $errMessages[] = "La contraseña brindada debe contener como minimo 8 caracteres";
                }
                if (strlen($contraseña) > 100) {
                    $error = true;
                    $errMessages[] = "La contraseña brindada es demasiado larga";
                }
                if ($error) {
                    return [
                        "state" => "forbidden",
                        "ErrMessage" => $errMessages
                    ];
                }

                $contraseña = password_hash($contraseña, PASSWORD_DEFAULT);

                $insertar = $this->pdo->prepare("insert into usuario(username, correo, contraseña, fechaCreacion, fechaNacimiento) values (:username, :correo, :contrasena, CURDATE(), CURDATE());");
                $insertar->execute(["username" => $username, "correo" => $correo, "contrasena" => $contraseña]);

                $expira = new DateTime();
                $expira->modify("+15 minutes");
                $expira = $expira->format("Y-m-d H:m:s");
                $codigo = $this->generarCodigoAcceso();
                
                $insertar = $this->pdo->prepare("insert into codigo_temporal(codigo, username, expira) values (:codigo, :username, :expira)");
                $insertar->execute(["codigo" => $codigo, "username" => $username, "expira" => $expira]);

                return [
                    "state" => "success",
                    "result" => $codigo
                ];
            } else {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => "El correo brindado ya esta asociada a una cuenta existente"
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

    public function generarCodigoAcceso(): string {
        do {
            $codigo = bin2hex(random_bytes(32));
            $solicitud = $this->pdo->prepare("select codigo from codigo_temporal where codigo = :codigo");
            $solicitud->execute(["codigo" => $codigo]);
            $existe = $solicitud->fetch() !== false;
        } while ($existe);

        return $codigo;
    }

    public function activarCuenta($codigo): array {
        $solicitud = $this->pdo->prepare("select username from codigo_temporal where codigo = :codigo");
        $solicitud->execute(["codigo" => $codigo]);
        $solicitud = $solicitud->fetch();

        if ($solicitud !== false) {
            $username = $solicitud["username"];

            $actualizar = $this->pdo->prepare("update usuario set verificado = 1 where username = :username");
            $actualizar->execute(["username" => $username]);

            $eliminar = $this->pdo->prepare("delete from codigo_temporal where username = :username");
            $eliminar->execute(["username" => $username]);

            return [
                "state" => "success"
            ];
        } else {
            return [
                "state" => "notFound",
                "ErrMessage" => "Codigo de activacion invalido"
            ];
        }
    }

    public function accederUsuario(string $username, string $contraseña, bool $mantener): array {
        if ($mantener === false) {
            $mantener = 0;
        } else {
            $mantener = 1;
        }

        if (strpos($username, ' ') !== false) {
            return [
                "state" => "forbidden",
                "ErrMessage" => "El nombre de usuario brindado no puede contener espacios"
            ];
        }

        $solicitud = $this->pdo->prepare("select username, contraseña, verificado from usuario where username = :username;");
        $solicitud->execute(["username" => $username]);
        $usuario = $solicitud->fetch();

        if ($usuario !== false) {
            if ($usuario["verificado"] == "1") {
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
                    "ErrMessage" => "Active su cuenta antes de iniciar sesion"
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

            $actualizar = $this->pdo->prepare("update juega set username = null where username = :username");
            $actualizar->execute(["username" => $username]);

            $actualizar = $this->pdo->prepare("update partida set host = null where host = :username");
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

        setcookie("golden-token", "", time() - 3600);

        return [
            "state" => "success"
        ];
    }
    public function editarUsuario(string $token, array $cambios): array {
        $credentials = $this->getUserCredentials($token);
        $mailer = new GBMailer();
        if ($credentials["state"] == "success") {
            $username = $credentials["result"]["username"];
            $correo = $credentials["result"]["correo"];
            $newname = $cambios["username"];
            $newcorreo = $cambios["correo"];
            $contraseña = $cambios["contraseña"];
            $descripcion = $cambios["descripcion"];

            $contraseña = password_hash($contraseña, PASSWORD_DEFAULT);

            $errMessages = [];
            $error = false;

            
            if (!filter_var($newcorreo, FILTER_VALIDATE_EMAIL)) {
                $error = true;
                $errMessages[] = "El formato del correo electronico no es valido";
            }
            if (strlen($newname) > 32) {
                $error = true;
                $errMessages[] = "El nombre de usuario brindado es demasiado largo";
            }
            if (strpos($newname, ' ') !== false) {
                $error = true;
                $errMessages[] = "El nombre de usuario brindado no puede contener espacios";
            }
            if (strlen($contraseña) < 8) {
                $error = true;
                $errMessages[] = "La contraseña brindada debe contener como minimo 8 caracteres";
            }
            if (strlen($contraseña) > 100) {
                $error = true;
                $errMessages[] = "La contraseña brindada es demasiado larga";
            }
            if (strlen($descripcion) > 200) {
                $error = true;
                $errMessages[] = "La descripcion brindada es demasiado larga";
            }
            if ($error) {
                return [
                    "state" => "forbidden",
                    "ErrMessage" => $errMessages
                ];
            } 
            
            // Desactiva los chequeos de la clave foranea
            $solicitud = $this->pdo->query("set foreign_key_checks = 0");

            // Actualiza la tabla usuario
            $actualizar = $this->pdo->prepare("update usuario set username = :newname where username = :username");
            $actualizar->execute(["newname" => $newname, "username" => $username]);

            // Actualiza la tabla partida
            $actualizar = $this->pdo->prepare("update partida set host = :newname where host = :username");
            $actualizar->execute(["newname" => $newname, "username" => $username]);

            // Actualiza la tabla juega
            $actualizar = $this->pdo->prepare("update juega set username = :newname where username = :username");
            $actualizar->execute(["newname" => $newname, "username" => $username]);

            // Actualiza la tabla conecta
            $actualizar = $this->pdo->prepare("update conecta set username = :newname where username = :username");
            $actualizar->execute(["newname" => $newname, "username" => $username]);

            // Actualiza la tabla inventario
            $actualizar = $this->pdo->prepare("update inventario set username = :newname where username = :username");
            $actualizar->execute(["newname" => $newname, "username" => $username]);

            // Actualiza la tabla tablero
            $actualizar = $this->pdo->prepare("update tablero set username = :newname where username = :username");
            $actualizar->execute(["newname" => $newname, "username" => $username]);

            // Actualiza la contraseña
            //$actualizar = $this->pdo->prepare("update usuario set contraseña = :contrasena where username = :username");
            //$actualizar->execute(["contrasena" => $contraseña, "username" => $username]);

            // Actualiza el correo
            //$actualizar = $this->pdo->prepare("update usuario set correo = :correo where username = :username");
            //$actualizar->execute(["correo" => $newcorreo, "username" => $username]);

            // Actualiza la descripcion
            $actualizar = $this->pdo->prepare("update usuario set descripcion = :descripcion where username = :username");
            $actualizar->execute(["descripcion" => $descripcion, "username" => $username]);

            $solicitud = $this->pdo->query("set foreign_key_checks = 1");

            // Notifica de los cambios a la cuenta de correo electronico actual y antigua
            //if ($correo == $newcorreo) {
                $mailer->cambiosCuenta($correo, $username);
            //} else {
            //    $mailer->cambiosCuenta($newcorreo, $username);
            //}

            return [
                "state" => "success"
            ];
        } else {
            return $credentials;
        }
    }
    
}