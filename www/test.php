<?php
include "php/clases/gbloomdb.php"; // Asegúrate de incluir tu clase

class GBloomDBTest extends GBloomDB {
    private $db;
    
    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    }
    
    public function runAllTests() {
        echo "🧪 INICIO PRUEBAS GBloomDB\n";
        echo str_repeat("=", 60) . "\n";
        
        $this->testTokenNoExiste();
        $this->testTokenExpirado();
        $this->testTokenValido();
        $this->testUsuarioNoExiste();
        
        echo "\n" . str_repeat("🎯", 30) . "\n";
        echo "PRUEBAS COMPLETADAS\n";
        echo str_repeat("🎯", 30) . "\n";
    }
    
    private function printTest($title, $result, $details = "") {
        $icon = $result ? "✅" : "❌";
        echo "$icon $title\n";
        if ($details) {
            echo "   📝 $details\n";
        }
    }
    
    private function testTokenNoExiste() {
        echo "\n" . str_repeat("-", 40) . "\n";
        echo "🔍 TEST 1: Token que NO EXISTE\n";
        echo str_repeat("-", 40) . "\n";
        
        try {
            $tokenInexistente = "token_que_no_existe_12345";
            $result = $this->getUserCredentials($tokenInexistente);
            
            $this->printTest("Debería fallar", false, "No se lanzó excepción");
            echo "   Resultado: " . json_encode($result) . "\n";
            
        } catch (Exception $e) {
            $errorData = json_decode($e->getMessage(), true);
            $this->printTest("Excepción lanzada", true);
            $this->printTest("Status 404", $errorData['status'] === 404);
            $this->printTest("Mensaje correcto", 
                strpos($errorData['ErrMessage'], 'no se encuentra') !== false);
            echo "   Error: " . $errorData['ErrMessage'] . "\n";
        }
    }
    
    private function testTokenExpirado() {
        echo "\n" . str_repeat("-", 40) . "\n";
        echo "🔍 TEST 2: Token EXPIRADO\n";
        echo str_repeat("-", 40) . "\n";
        
        try {
            // Primero insertamos un token expirado para la prueba
            $tokenExpirado = "token_expirado_test_" . uniqid();
            $username = "usuario_test";
            $fechaExpiracion = (new DateTime())->modify("-1 day")->format("Y-m-d H:i:s");
            $fechaCreacion = (new DateTime())->modify("-2 days")->format("Y-m-d H:i:s");
            
            // Ejecutar prueba
            $result = $this->getUserCredentials($tokenExpirado);
            
            $this->printTest("Debería fallar", false, "No se lanzó excepción");
            echo "   Resultado: " . json_encode($result) . "\n";
            
        } catch (Exception $e) {
            $errorData = json_decode($e->getMessage(), true);
            $this->printTest("Excepción lanzada", true);
            $this->printTest("Status 401", $errorData['status'] === 401); // unauthorized
            $this->printTest("Mensaje de expiración", 
                strpos($errorData['ErrMessage'], 'expiracion') !== false);
            echo "   Error: " . $errorData['ErrMessage'] . "\n";
            
            // Verificar que el token fue eliminado
            $check = $this->pdo->prepare("SELECT COUNT(*) as count FROM sesion WHERE token = :token");
            $check->execute(['token' => $tokenExpirado]);
            $exists = $check->fetch()['count'] > 0;
            $this->printTest("Token eliminado automáticamente", !$exists);
        }
    }
    
    private function testTokenValido() {
        echo "\n" . str_repeat("-", 40) . "\n";
        echo "🔍 TEST 3: Token VÁLIDO\n";
        echo str_repeat("-", 40) . "\n";
        
        try {
            // Crear datos de prueba
            $tokenValido = "token_valido_test_" . uniqid();
            $username = "usuario_test_" . uniqid();
            $fechaExpiracion = (new DateTime())->modify("+1 hour")->format("Y-m-d H:i:s");
            $fechaCreacion = (new DateTime())->format("Y-m-d H:i:s");
            
            // Insertar usuario de prueb
            
            
            // Ejecutar prueba
            $result = $this->getUserCredentials($tokenValido);
            
            $this->printTest("Éxito en la operación", $result['status'] === 'success');
            $this->printTest("Datos de usuario retornados", !empty($result['result']));
            $this->printTest("Username correcto", $result['result']['username'] === $username);
            
            echo "   Resultado: " . json_encode(['status' => $result['status'], 'username' => $result['result']['username']]) . "\n";
            
        } catch (Exception $e) {
            $this->printTest("No debería fallar", false, "Excepción inesperada: " . $e->getMessage());
        }
    }
    
    private function testUsuarioNoExiste() {
        echo "\n" . str_repeat("-", 40) . "\n";
        echo "🔍 TEST 4: Token VÁLIDO pero Usuario NO EXISTE\n";
        echo str_repeat("-", 40) . "\n";
        
        try {
            // Crear token para usuario que no existe
            $tokenValido = "token_usuario_inexistente_" . uniqid();
            $usernameInexistente = "usuario_que_no_existe_" . uniqid();
            $fechaExpiracion = (new DateTime())->modify("+1 hour")->format("Y-m-d H:i:s");
            $fechaCreacion = (new DateTime())->format("Y-m-d H:i:s");
            
            // Ejecutar prueba
            $result = $this->getUserCredentials($tokenValido);
            
            $this->printTest("Debería fallar", false, "No se lanzó excepción");
            echo "   Resultado: " . json_encode($result) . "\n";
            
        } catch (Exception $e) {
            $errorData = json_decode($e->getMessage(), true);
            $this->printTest("Excepción lanzada", true);
            $this->printTest("Status 404", $errorData['status'] === 404);
            $this->printTest("Mensaje de usuario no encontrado", 
                strpos($errorData['ErrMessage'], 'usuario') !== false);
            echo "   Error: " . $errorData['ErrMessage'] . "\n";
        } finally {
            // Limpiar
        }
    }
}

// Ejecutar pruebas
echo "🚀 INICIANDO PRUEBAS AUTOMÁTICAS GBloomDB\n";

$test = new GBloomDBTest();
$test->runAllTests();

// Test adicional con casos específicos
echo "\n" . str_repeat("🔧", 30) . "\n";
echo "PRUEBA MANUAL RÁPIDA\n";
echo str_repeat("🔧", 30) . "\n";

// Prueba manual con un token específico
try {
    $testDB = new GBloomDB("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    
    // Cambia este token por uno que exista en tu base de datos para probar
    $tokenEjemplo = "testo";
    $resultado = $testDB->getUserCredentials($tokenEjemplo);
    
    var_dump($resultado);
    
} catch (Exception $e) {
    $error = json_decode($e->getMessage(), true);
    var_dump($error);
}
?>

<!-- Index.html file -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"
          href="style.css">
    <title>QR Code Scanner / Reader
    </title>
</head>

<body>
    <div class="container">
        <h1>Scan QR Codes</h1>
        <div class="section">
            <div id="my-qr-reader">
            </div>
        </div>
    </div>
    <script
        src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js">
    </script>
    <script>
        // script.js file

function domReady(fn) {
    if (
        document.readyState === "complete" ||
        document.readyState === "interactive"
    ) {
        setTimeout(fn, 1000);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

domReady(function () {

    // If found you qr code
    function onScanSuccess(decodeText, decodeResult) {
        alert("You Qr is : " + decodeText, decodeResult);
    }

    let htmlscanner = new Html5QrcodeScanner(
        "my-qr-reader",
        { fps: 10, qrbos: 250 }
    );
    htmlscanner.render(onScanSuccess);
});
    </script>
</body>

</html>

<!DOCTYPE html>
<html>
<head>
    <title>QR Code Generator</title>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
</head>
<body>
    <input type="text" id="text" value="Hello, World!" style="width: 80%"><br>
    <div id="qrcode" style="width:100px; height:100px; margin-top:15px;"></div>

    <script type="text/javascript">
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            width : 100,
            height : 100
        });

        function makeCode () {
            var elText = document.getElementById("text");
            if (!elText.value) {
                alert("Input a text");
                elText.focus();
                return;
            }
            qrcode.makeCode(elText.value);
        }

        makeCode(); // Generate initial QR code
        document.getElementById("text").addEventListener("keyup", makeCode); // Update on input change
    </script>
</body>
</html>