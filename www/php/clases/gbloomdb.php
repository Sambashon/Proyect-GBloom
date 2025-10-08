<?php
include "gerror.php";
class GBloomDB {
    protected PDO $pdo;
    public array $success;
    protected GError $notFound;
    private GError $expiration;
    private GError $invalidToken;

    public function __construct(string $host, string $username, string $dbname, string $password, int $port) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4",
            $username,
            $password,
            $options
        );

        $this->success = [
            "status" => "success",
            "result" => null
        ];

        $this->notFound = new GError(
            "Not Found",
            GError::notFound,
            GError::inclusive,
            [
                true => "La base de datos no devolvio ningun dato",
                "La base de datos no devolvio ningun dato" => fn($input): bool => !empty($input)
            ],
            "Data Base Output Filter",
            true
        );

        $this->invalidToken = new GError(
            "Invalid Token",
            GError::notFound,
            GError::inclusive,
            [
                true => "La base de datos no devolvio ningun dato",
                "La base de datos no devolvio ningun dato" => function ($input): bool {
                    setcookie("golden-token", "", time() - 3600);
                    
                    return !empty($input);
                }
            ],
            "Token Filter",
            true,
            "El usuario indexado no se encuentra en la base de datos"
        );

        $this->expiration = new GError(
            "Expiration Filter",
            GError::unauthorized,
            GError::exclusive,
            [
                "token_expirado" => function (array $input): bool {
                        if (new DateTime($input["expira"]) < new DateTime()) {
                            $accion = $this->pdo->prepare("delete from sesion where token = :token;");
                            $accion->execute(["token" => $input["token"]]);

                            setcookie("golden-token", "", time() - 3600);

                            return true;
                        } else {
                            return false;
                        }
                    }
            ],
            "Expiration Filter",
            true,
            "El token indexado ha alcanzado su fecha de expiracion"
        );
    }

    public function getUserCredentials(string $token): array {
        $solicitud = $this->pdo->prepare("select username, expira, fecha, mantener from sesion where token = :token;");
        $solicitud->execute(["token" => $token]);
        $userinfo = $this->invalidToken->filter($solicitud->fetch());
        
        $this->expiration->filter(["token" => $token, "expira" => $userinfo["expira"]]);

        if ($userinfo["mantener"] == "1") {
            $ahora = new DateTime();
            $desde = $ahora->diff(new DateTime($userinfo["fecha"]));
            $expira = (new DateTime())->modify("+1 months")->format("Y-m-d H:i:s");
            if ($desde->days > 1) {
                $actualizar = $this->pdo->prepare("update sesion set expira = :expira where token = :token;");
                $actualizar->execute(["expira" => $expira, "token" => $token]);
            }
        }

        $solicitud = $this->pdo->prepare("select * from usuario where username = :username;");
        $solicitud->execute(["username" => $userinfo["username"]]);

        $this->notFound->setOrigin("GBloomDB->getUserCredentials()");
        $usuario = $this->notFound->filter($solicitud->fetch());

        return $this->returnSuccess($usuario);
    }

    public function returnSuccess(mixed $result): array {
        $this->success["result"] = $result;
        return $this->success;
    }

}