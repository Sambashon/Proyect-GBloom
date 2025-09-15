<?php
class GBloomDB {
    protected PDO $pdo;

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
    }

    public function getUserCredentials(string $token): array {
        $solicitud = $this->pdo->prepare("select username, expira, fecha, mantener from sesion where token = :token;");
        $solicitud->execute(["token" => $token]);
        $userinfo = $solicitud->fetch();
        if ($userinfo !== false) {
            if (new DateTime($userinfo["expira"]) < new DateTime()) {
                $acccion = $this->pdo->prepare("delete from sesion where token = :token;");
                $acccion->execute(["token" => $token]);

                setcookie("golden-token", "", time() - 3600);

                return [
                    "state" => "expired",
                    "ErrMessage" => "El token indexado ha alcanzado su fecha de expiracion"
                ];
            }

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
            $usuario = $solicitud->fetch();

            if ($usuario !== false) {
                return [
                    "state" => "success",
                    "result" => $usuario
                ];
            } else {
                return [
                    "state" => "notFound",
                    "ErrMessage" => "El usuario indexado no se encuentra en la base de datos"
                ];
            }
        }
        else {
            setcookie("golden-token", "", time() - 3600);

            return [
                "state" => "notFound",
                "ErrMessage" => "El token indexado no se encuentra en la base de datos"
            ];
        }
    }

}