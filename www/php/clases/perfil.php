<?php
include "gbloomdb.php";
include "gbmailer.php";

class Perfil extends GBloomDB {

    private GError $filtroRegistro;
    private GError $filtroAccesso;
    private GBMailer $mailer;

    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    
        $this->mailer = new GBMailer();

        // Cambiar filtro para que aunque falte un parametro funcione (SINO SE ROMPE TODO)
        $this->filtroRegistro = new GError(
            "Filtro de Registro",
            GError::forbidden,
            GError::all_match,
            [
            "El formato del correo electronico no es valido" => fn($input) => 
                filter_var($input['correo'] ?? 'test@test.test', FILTER_VALIDATE_EMAIL),
            
            "El nombre de usuario brindado es demasiado largo" => fn($input) => 
                strlen($input['username'] ?? '') <= 32,
            
            "El nombre de usuario brindado no puede contener espacios" => fn($input) => 
                strpos($input['username'] ?? '', ' ') === false,
            
            "La contraseña brindada debe contener como minimo 8 caracteres" => fn($input) => 
                strlen($input['contraseña'] ?? '12345678') >= 8,
            
            "La contraseña brindada es demasiado larga" => fn($input) => 
                strlen($input['contraseña'] ?? '') <= 100,
            
            "La descripcion brindada es demasiado larga" => fn($input) => 
                strlen($input['descripcion'] ?? '') <= 200,
            
            "La fecha de nacimiento brindada no es valida" => function($input) {
                    $fecha = $input['fechaNacimiento'] ?? '1900-01-01';
                    
                    // Solo validar formato y que sea fecha válida
                    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) &&
                        ($fechaObj = DateTime::createFromFormat('Y-m-d', $fecha)) &&
                        $fechaObj->format('Y-m-d') === $fecha;
                }
            ],
            "Perfil->registrarUsuario()",
            true
        );

        $this->filtroAccesso = new GError(
            "Acceso a Cuenta",
            GError::forbidden,
            GError::all_match,
            [
        "La contraseña o el usuario ingresados no son validos" => fn($input) => 
            !empty($input['usuario']) && password_verify($input['contraseña'], $input['usuario']['contraseña']),
        
        "Active su cuenta antes de iniciar sesion" => fn($input) => 
            !empty($input['usuario']) && $input['usuario']['verificado'] == "1"
    ],
            "Perfil->accederUsuario()",
            true,
            "Acceso Denegado"
        );
    }

    public function registrarUsuario(string $username, string $contraseña, string $correo): array {
        $solicitud = $this->pdo->prepare("select username from usuario where username = :username;");
        $solicitud->execute(["username" => $username]);

        $this->notFound->setOrigin("Perfil->registrarUsuario()");
        $this->notFound->setFilterType(GError::exclusive);
        $this->notFound->setErrMessage("El nombre de usuario no se puede registrar porque ya existe");
        $existe = $this->notFound->filter($solicitud->fetch());

        $solicitud = $this->pdo->prepare("select correo from usuario where correo = :correo;");
        $solicitud->execute(["correo" => $correo]);
        $this->notFound->setErrMessage("El correo brindado ya esta asociada a una cuenta existente");
        $existe = $this->notFound->filter($solicitud->fetch());

        $this->filtroRegistro->filter(["username" => $username, "contraseña" => $contraseña, "correo" => $correo]);
        $this->notFound->setFilterType(GError::inclusive);

        $contraseña = password_hash($contraseña, PASSWORD_DEFAULT);

        $insertar = $this->pdo->prepare("insert into usuario(username, correo, contraseña, fechaCreacion, fechaNacimiento) values (:username, :correo, :contrasena, CURDATE(), CURDATE());");
        $insertar->execute(["username" => $username, "correo" => $correo, "contrasena" => $contraseña]);

        $expira = new DateTime();
        $expira->modify("+15 minutes");
        $expira = $expira->format("Y-m-d H:m:s");
        $codigo = $this->generarCodigoAcceso();
        
        $insertar = $this->pdo->prepare("insert into codigo_temporal(codigo, username, expira) values (:codigo, :username, :expira)");
        $insertar->execute(["codigo" => $codigo, "username" => $username, "expira" => $expira]);

        return $this->returnSuccess($codigo);
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

        $this->notFound->setOrigin("Perfil->activarCuenta()");
        $this->notFound->setErrMessage("Codigo de activacion invalido");
        $solicitud = $this->notFound->filter($solicitud->fetch());

        $username = $solicitud["username"];

        $actualizar = $this->pdo->prepare("update usuario set verificado = 1 where username = :username");
        $actualizar->execute(["username" => $username]);

        $eliminar = $this->pdo->prepare("delete from codigo_temporal where username = :username");
        $eliminar->execute(["username" => $username]);

        return $this->returnSuccess(null);
    }

    public function accederUsuario(string $username, string $contraseña, bool $mantener): array {
        $mantener = $mantener ? 1 : 0;

        $this->filtroRegistro->setOrigin("Perfil->accederUsuario()");
        $this->filtroRegistro->filter(["username" => $username]);

        $solicitud = $this->pdo->prepare("select username, contraseña, verificado from usuario where username = :username;");
        $solicitud->execute(["username" => $username]);
        $usuario = $solicitud->fetch();

        $this->notFound->setOrigin("Perfil->accederUsuario()");
        $this->notFound->setErrMessage("La contraseña o el usuario ingresados no son validos");
        $this->notFound->filter($usuario);
        $this->filtroAccesso->filter(["contraseña" => $contraseña, "usuario" => $usuario]);

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

        return $this->returnSuccess(["token" => $token, "expira" => $expira]);
    }

    public function eliminarUsuario(string $token): array {
        $credentials = $this->getUserCredentials($token);

        $username = $credentials["result"]["username"];
        $correo = $credentials["result"]["correo"];

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

        $this->mailer->cuentaEliminada($correo, $username);

        return $this->returnSuccess(null);
    }

    public function cerrarSesion(string $token): array {
        $eliminar = $this->pdo->prepare("delete from sesion where token = :token");
        $eliminar->execute(["token" => $token]);

        setcookie("golden-token", "", time() - 3600);

        return $this->returnSuccess(null);
    }
    public function editarUsuario(string $token, array $cambios): array {
        $credentials = $this->getUserCredentials($token);
        $username = $credentials["result"]["username"];
        $correo = $credentials["result"]["correo"];
        $newname = $cambios["username"];
        $fechaNacimiento = $cambios["fechaNacimiento"];
         //$newcorreo = $cambios["correo"];
        //$contraseña = $cambios["contraseña"];
        $descripcion = $cambios["descripcion"];

        //$contraseña = password_hash($contraseña, PASSWORD_DEFAULT);

        $this->filtroRegistro->filter(["username" => $newname, "descripcion" => $descripcion]);
        
        // Desactiva los chequeos de la clave foranea
        $this->pdo->query("set foreign_key_checks = 0");

        // Actualiza la tabla usuario
        $actualizar = $this->pdo->prepare("update usuario set username = :newname where username = :username");
        $actualizar->execute(["newname" => $newname, "username" => $username]);

        $actualizar = $this->pdo->prepare("update sesion set username = :newname where token = :token");
        $actualizar->execute(["newname" => $newname, "token" => $token]);

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

        // Actualiza la fecha de nacimiento
        $actualizar = $this->pdo->prepare("update usuario set fechaNacimiento = :fechaNacimiento where username = :username");
        $actualizar->execute(["fechaNacimiento" => $fechaNacimiento, "username" => $username]);

        $this->pdo->query("set foreign_key_checks = 1");

        // Notifica de los cambios a la cuenta de correo electronico actual y antigua
        //if ($correo == $newcorreo) {
        if ($username != $newname) {
            $this->mailer->cambiosCuenta($correo, $username);
        }
        //} else {
        //    $mailer->cambiosCuenta($newcorreo, $username);
        //}

        return $this->returnSuccess(null);
    }
    
}