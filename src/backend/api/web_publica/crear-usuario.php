<?php

use Ramsey\Uuid\Uuid;

// CORS + tipos
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");

// Responder preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function clientIp(): string
{
    // Prioriza X-Forwarded-For si hay proxy/CDN; toma el primer IP no vacío
    $candidates = [
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
        $_SERVER['HTTP_CLIENT_IP'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '',
    ];
    foreach ($candidates as $c) {
        if ($c) {
            // Puede venir como "ip1, ip2"; tomamos la primera limpia
            $parts = array_map('trim', explode(',', $c));
            foreach ($parts as $p) {
                if ($p !== '') return $p;
            }
        }
    }
    return 'Desconegut';
}

function detectBrowser(string $ua): string
{
    // Orden importante: Edge (Edg) -> Chrome/CriOS -> Safari -> Firefox
    if (stripos($ua, 'Edg/') !== false || stripos($ua, 'Edge/') !== false) return 'Edge';
    if (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera') !== false) return 'Opera';
    if (stripos($ua, 'CriOS/') !== false) return 'Chrome'; // Chrome en iOS
    if (stripos($ua, 'Chrome/') !== false) return 'Chrome';
    if (stripos($ua, 'FxiOS/') !== false) return 'Firefox'; // Firefox en iOS
    if (stripos($ua, 'Firefox/') !== false) return 'Firefox';
    // Safari debe ir después de Chrome (porque Chrome contiene 'Safari')
    if (stripos($ua, 'Safari/') !== false) return 'Safari';
    return 'Desconegut';
}

function detectOSFromUA(string $ua): string
{
    $ua = strtolower($ua);
    // Móvil primero
    if (strpos($ua, 'android') !== false) return 'Android';
    if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false || strpos($ua, 'ipod') !== false) return 'iOS';

    // Escritorio / otros
    if (strpos($ua, 'windows nt') !== false || strpos($ua, 'windows') !== false) return 'Windows';
    if (strpos($ua, 'mac os x') !== false || strpos($ua, 'macintosh') !== false) return 'macOS';
    if (strpos($ua, 'cros') !== false) return 'ChromeOS';
    if (strpos($ua, 'linux') !== false) return 'Linux';

    return 'Desconegut';
}

function detectDeviceType(string $ua): string
{
    return (preg_match('/mobile|android|iphone|ipad|ipod/i', $ua)) ? 'Mòbil' : 'Escriptori';
}

function getUserInfo(): array
{
    $uaRaw = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = clientIp();

    // Client Hints (si están disponibles)
    $chPlatform = $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '';
    $chMobile   = $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '';
    // Valores de CH suelen venir entre comillas, límpialos:
    $chPlatform = trim($chPlatform, '"\'');

    $so = $chPlatform !== '' ? $chPlatform : detectOSFromUA($uaRaw);
    // Normaliza nombres
    if (strcasecmp($so, 'mac os') === 0) $so = 'macOS';

    $navegador = detectBrowser($uaRaw);
    $dispositivo = ($chMobile === '?1') ? 'Mòbil' : detectDeviceType($uaRaw);

    return [
        'ip' => $ip,
        'navegador' => $navegador,
        'sistema_operatiu' => $so,
        'dispositiu' => $dispositivo,
        // opcionalmente guarda el UA crudo si tienes columna:
        'user_agent_raw' => $uaRaw,
    ];
}


// Leer el cuerpo de la solicitud JSON
$data = json_decode(file_get_contents("php://input"), true);

// Verificar que los datos se recibieron correctamente
if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "No se enviaron datos válidos.",
        "errors" => []
    ]);
    exit;
}

$errors = [];

// Validar y sanitizar datos recibidos
$hasError = false;

// validar camps obligatoris
// Validar nombre
if (empty($data["nombre"])) {
    $errors["nombre"] = "El nombre es obligatorio.";
    $hasError = true;
} elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $data["nombre"])) {
    $errors["nombre"] = "El nombre debe contener solo letras y espacios.";
    $hasError = true;
}

// Validar email
if (empty($data["email"])) {
    $errors["email"] = "El correo electrónico es obligatorio.";
    $hasError = true;
} elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "El correo electrónico no es válido.";
    $hasError = true;
}

// Validar teléfono
if (empty($data["telefono"])) {
    $errors["telefono"] = "El teléfono es obligatorio.";
    $hasError = true;
} elseif (!preg_match("/^[0-9]{9,15}$/", $data["telefono"])) {
    $errors["telefono"] = "El teléfono debe contener solo números y tener entre 9 y 15 dígitos.";
    $hasError = true;
}

// Si hay errores, enviarlos al cliente
if (!empty($errors)) {
    echo json_encode([
        "status" => "error",
        "message" => "Errores en los datos enviados.",
        "errors" => $errors
    ]);
    exit;
}

// si no hi ha errors, continuem amb la validacio de les dades

$uuidObj = Uuid::uuid7();
$uuidBytes = $uuidObj->getBytes();      // BINARY(16)
$uuidStr   = $uuidObj->toString();      // para devolver al frontend (legible)
$estado    = 'activo';

$nombre = data_input($data["nombre"]);
$email = data_input($data["email"]);
$telefono = data_input($data["telefono"]);

$empresa = !empty($data["empresa"]) ? data_input($data["empresa"]) : null;
$nif = !empty($data["nif"]) ? data_input($data["nif"]) : null;
$direccion = !empty($data["direccion"]) ? data_input($data["direccion"]) : null;
$ciudad = !empty($data["ciudad"]) ? data_input($data["ciudad"]) : null;
$codigo_postal = !empty($data["codigo_postal"]) ? data_input($data["codigo_postal"]) : null;
$pais = !empty($data["pais"]) ? data_input($data["pais"]) : null;

$tipoUsuario = 'cliente'; // Asignar tipo de usuario por defecto
$locale = 'es';

// informacion tecnica usuario
$info = getUserInfo();

$dispositiu = $info['dispositiu'];
$navegador = $info['navegador'];
$sistema_operatiu = $info['sistema_operatiu'];
$ip = $info['ip'];

// Si hay errores en los datos, devolver una respuesta de error
if ($hasError) {
    echo json_encode([
        "status" => "error",
        "message" => "Datos incompletos."
    ]);
    exit;
}

global $conn;

// 🔁 CAMBIO IMPORTANTE: nueva BD + nueva tabla
$sql = "INSERT INTO usuarios SET uuid = :uuid, estado=:estado, nombre=:nombre, email=:email, empresa=:empresa, nif=:nif, direccion=:direccion, ciudad=:ciudad, codigo_postal=:codigo_postal, pais=:pais, telefono=:telefono, tipo_rol=:tipo_rol, locale=:locale, dispositiu=:dispositiu, navegador=:navegador, sistema_operatiu=:sistema_operatiu, ip=:ip";

$stmt = $conn->prepare($sql);
$stmt->bindValue(":uuid", $uuidBytes, PDO::PARAM_LOB);
$stmt->bindValue(":estado", $estado, PDO::PARAM_STR);
$stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
$stmt->bindParam(":email", $email, PDO::PARAM_STR);
$stmt->bindParam(":empresa", $empresa, PDO::PARAM_STR);
$stmt->bindParam(":nif", $nif, PDO::PARAM_STR);
$stmt->bindParam(":direccion", $direccion, PDO::PARAM_STR);
$stmt->bindParam(":ciudad", $ciudad, PDO::PARAM_STR);
$stmt->bindParam(":codigo_postal", $codigo_postal, PDO::PARAM_STR);
$stmt->bindParam(":pais", $pais, PDO::PARAM_STR);
$stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
$stmt->bindParam(":tipo_rol", $tipoUsuario, PDO::PARAM_STR);
$stmt->bindParam(":locale", $locale, PDO::PARAM_STR);
$stmt->bindParam(":dispositiu", $dispositiu, PDO::PARAM_STR);
$stmt->bindParam(":navegador", $navegador, PDO::PARAM_STR);
$stmt->bindParam(":sistema_operatiu", $sistema_operatiu, PDO::PARAM_STR);
$stmt->bindParam(":ip", $ip, PDO::PARAM_STR);

if ($stmt->execute()) {

    // response output
    // Devolver respuesta de éxito
    echo json_encode([
        "status" => "success",
        "usuario_uuid_hex" => $uuidStr,
        "message" => "Cliente creado con exito."
    ]);
} else {
    // response output - data error
    echo json_encode([
        "status" => "error",
        "message" => "Error en la base de datos."
    ]);
}
