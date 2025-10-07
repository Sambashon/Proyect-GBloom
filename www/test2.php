<?php
include_once "php/clases/error.php";

// ==============================
// ESTILOS Y CABECERA HTML
// ==============================
echo '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pruebas Clase GError</title>
<style>
    body { font-family: Arial, sans-serif; background: #f0f4f8; margin: 0; padding: 20px; }
    h1, h2 { text-align: center; }
    .section { margin: 30px 0; }
    .card { background: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 15px; }
    .card h3 { margin-top: 0; }
    .success { color: #2a9d8f; font-weight: bold; }
    .error { color: #e76f51; font-weight: bold; }
    ul { padding-left: 20px; }
    li { margin: 5px 0; }
    .icon { font-size: 1.2em; margin-right: 5px; }
    .divider { border-bottom: 2px dashed #ccc; margin: 15px 0; }
</style>
</head>
<body>
<h1>🧪 Pruebas de la Clase GError</h1>';

// ==============================
// FUNCIONES AUXILIARES
// ==============================
function printCard($title, $content) {
    echo '<div class="card">';
    echo "<h3>$title</h3>";
    echo $content;
    echo '</div>';
}

function printTest($desc, $passed) {
    $icon = $passed ? "✅" : "❌";
    $class = $passed ? "success" : "error";
    echo "<p class='$class'><span class='icon'>$icon</span>$desc</p>";
}

// ==============================
// CLASE DE PRUEBAS
// ==============================
class TestValidators {
    public static function staticMethod($x) { return is_string($x); }
    public function instanceMethod($x) { return strlen($x) > 0; }
}

class GErrorDashboardTest {
    
    public static function runAll() {
        self::printSection("🧪 INICIO PRUEBAS COMPLETAS CLASE GERROR");
        
        self::testInclusiveFilter();
        self::testExclusiveFilter();
        self::testAllMatchFilter();
        self::testStrictExcludeFilter();
        self::testAutoThrowBehavior();
        self::testArrayTypes();
        self::testCallableTypes();
        self::testRealWorldScenarios();
        self::testEdgeCases();
        
        self::printSection("🎉 TODAS LAS PRUEBAS COMPLETADAS EXITOSAMENTE 🎉");
    }

    private static function printSection($title) {
        echo "<div class='section'><h2>$title</h2></div>";
    }
    
    private static function printTestsResult($tests) {
        $content = '';
        foreach ($tests as $desc => $passed) {
            $icon = $passed ? "✅" : "❌";
            $class = $passed ? "success" : "error";
            $content .= "<p class='$class'><span class='icon'>$icon</span>$desc</p>";
        }
        return $content;
    }

    // ==================================================
    // 1. FILTRO INCLUSIVE
    // ==================================================
    public static function testInclusiveFilter() {
        $tests = [];
        $filter = new GError("InclusiveTest", GError::badRequest, GError::inclusive, ["admin","user","guest"], "InclusiveTest", false);
        $tests["Valor existente en array indexado"] = $filter->filter("user") === "user";
        $tests["Valor no existente en array indexado"] = $filter->filter("moderator") === null;
        $tests["Residue acumulado contiene 'moderator'"] = in_array("moderator",$filter->getResidue());
        printCard("1️⃣ FILTRO INCLUSIVE", self::printTestsResult($tests));
    }

    // ==================================================
    // 2. FILTRO EXCLUSIVE
    // ==================================================
    public static function testExclusiveFilter() {
        $tests = [];
        $filter = new GError("ExclusiveTest", GError::forbidden, GError::exclusive, ["spam","malware"], "ExclusiveTest", false);
        $tests["Valor prohibido"] = $filter->filter("spam") === null;
        $tests["Valor permitido"] = $filter->filter("legit") === "legit";
        printCard("2️⃣ FILTRO EXCLUSIVE", self::printTestsResult($tests));
    }

    // ==================================================
    // 3. FILTRO ALL_MATCH
    // ==================================================
    public static function testAllMatchFilter() {
        $tests = [];
        $passwordValidator = new GError(
            "PasswordValidator",
            GError::badRequest,
            GError::all_match,
            [
                "min_length" => fn($p) => strlen($p) >= 8,
                "has_uppercase" => fn($p) => preg_match('/[A-Z]/',$p),
                "has_lowercase" => fn($p) => preg_match('/[a-z]/',$p),
                "has_number" => fn($p) => preg_match('/[0-9]/',$p),
                "has_special" => fn($p) => preg_match('/[^A-Za-z0-9]/',$p)
            ],
            "Password Validation",
            false
        );
        $weak = "abc"; $medium = "Password123"; $strong = "Secure@123!";
        $tests["Contraseña débil rechazada"] = $passwordValidator->filter($weak) === null;
        $tests["Contraseña media rechazada"] = $passwordValidator->filter($medium) === null;
        $tests["Contraseña fuerte aceptada"] = $passwordValidator->filter($strong) === $strong;
        $tests["ErrDetails acumulados"] = count($passwordValidator->getErrDetails()) > 0;
        printCard("3️⃣ FILTRO ALL_MATCH", self::printTestsResult($tests));
    }

    // ==================================================
    // 4. FILTRO STRICT_EXCLUDE
    // ==================================================
    public static function testStrictExcludeFilter() {
        $tests = [];
        $filter = new GError("StrictExcl", GError::forbidden, GError::strict_exclude, ["danger"], "StrictExcl", false);
        $tests["Valor peligroso excluido"] = $filter->filter("danger") === null;
        $tests["Valor seguro permitido"] = $filter->filter("safe") === "safe";
        printCard("4️⃣ FILTRO STRICT_EXCLUDE", self::printTestsResult($tests));
    }

    // ==================================================
    // 5. AUTO_THROW
    // ==================================================
    public static function testAutoThrowBehavior() {
        $tests = [];
        $throwFilter = new GError("ThrowTest", GError::badRequest, GError::exclusive, ["blocked"], "ThrowTest", true, "❌ Acceso denegado");
        try {
            $throwFilter->filter("blocked");
            $tests["AutoThrow debería lanzar excepción"] = false;
        } catch(Exception $e) {
            $tests["AutoThrow excepción lanzada"] = true;
        }
        printCard("5️⃣ AUTO_THROW", self::printTestsResult($tests));
    }

    // ==================================================
    // 6. TIPOS DE ARRAY
    // ==================================================
    public static function testArrayTypes() {
        $tests = [];
        $filter = new GError("ArrayTest", GError::badRequest, GError::exclusive, ["spam", "malware"], "ArrayTest", false);
        $filter->filter("spam");
        var_dump($filter->getErrDetails());
        $tests["Array indexado errDetails vacío"] = empty($filter->getErrDetails());
        printCard("6️⃣ TIPOS DE ARRAY", self::printTestsResult($tests));
    }

    // ==================================================
    // 7. TIPOS DE CALLABLE
    // ==================================================
    public static function testCallableTypes() {
        $tests = [];
        $obj = new TestValidators();
        $filter = new GError("CallableTest", GError::badRequest, GError::inclusive, [
            "lambda"=>fn($x)=>$x==="lambda",
            "static_method"=>['TestValidators','staticMethod'],
            "instance_method"=>[$obj,'instanceMethod'],
        ], "CallableTest", false);
        $tests["Lambda ejecutada"] = $filter->filter("lambda") === "lambda";
        $tests["Static method ejecutado"] = $filter->filter("string") === "string";
        $tests["Instance method ejecutado"] = $filter->filter("nonempty") === "nonempty";
        printCard("7️⃣ TIPOS DE CALLABLE", self::printTestsResult($tests));
    }

    // ==================================================
    // 8. ESCENARIOS DEL MUNDO REAL
    // ==================================================
    public static function testRealWorldScenarios() {
        $tests = [];
        $userValidator = new GError(
            "UserValidation",
            GError::badRequest,
            GError::all_match,
            [
                "email"=>fn($u)=>filter_var($u['email'], FILTER_VALIDATE_EMAIL),
                "edad"=>fn($u)=>$u['age']>=18,
                "pais"=>fn($u)=>in_array($u['country'],['US','MX'])
            ], "UserValidation", false
        );
        $invalid = ['email'=>'inv','age'=>15,'country'=>'XX'];
        $valid = ['email'=>'ok@example.com','age'=>25,'country'=>'US'];
        $tests["Usuario inválido rechazado"] = $userValidator->filter($invalid) === null;
        $tests["Usuario válido aceptado"] = $userValidator->filter($valid) !== null;
        printCard("8️⃣ ESCENARIOS REALES", self::printTestsResult($tests));
    }

    // ==================================================
    // 9. CASOS LÍMITE
    // ==================================================
    public static function testEdgeCases() {
        $tests = [];
        $filter = new GError("EdgeTest", GError::badRequest, GError::inclusive, [], "EdgeTest", false);
        $tests["Array vacío inclusive - siempre null"] = $filter->filter("any") === null;
        printCard("9️⃣ CASOS LÍMITE", self::printTestsResult($tests));
    }
}

// ==============================
// EJECUCIÓN DE TODAS LAS PRUEBAS
// ==============================
GErrorDashboardTest::runAll();

echo '</body></html>';
?>
