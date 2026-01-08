<?php
declare(strict_types=1);

requireMethod('PUT');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}

const USUARIOS_ROL_ALLOWED     = ['cliente','cliente_anual','trabajador','admin'];
const USUARIOS_LOCALE_ALLOWED  = ['ca','es','fr','en','it'];
const ESTADO_FORZADO_INTRANET  = 'activo';

/* =========================
   Helpers comunes
========================= */
function normalizarEmail(string $email): string {
    return mb_strtolower(trim($email));
}
function validarEmail(string $email): bool {
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}
function opt(array $input, string $k): ?string {
    if (!isset($input[$k])) return null;
    $v = trim((string)$input[$k]);
    return $v === '' ? null : $v;
}
function clientIp(): string {
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}
function userAgent(): string {
    return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}
function detectarDispositivo(string $ua): ?string {
    $u = mb_strtolower($ua);
    if ($u === '') return null;
    if (str_contains($u, 'mobile') || str_contains($u, 'iphone') || str_contains($u, 'android')) return 'mobile';
    if (str_contains($u, 'ipad') || str_contains($u, 'tablet')) return 'tablet';
    return 'desktop';
}
function detectarNavegador(string $ua): ?string {
    $u = mb_strtolower($ua);
    if ($u === '') return null;
    if (str_contains($u, 'edg/')) return 'Edge';
    if (str_contains($u, 'chrome/') && !str_contains($u, 'edg/')) return 'Chrome';
    if (str_contains($u, 'firefox/')) return 'Firefox';
    if (str_contains($u, 'safari/') && !str_contains($u, 'chrome/')) return 'Safari';
    return null;
}
function detectarSistemaOperativo(string $ua): ?string {
    $u = mb_strtolower($ua);
    if ($u === '') return null;
    if (str_contains($u, 'windows')) return 'Windows';
    if (str_contains($u, 'mac os') || str_contains($u, 'macintosh')) return 'macOS';
    if (str_contains($u, 'android')) return 'Android';
    if (str_contains($u, 'iphone') || str_contains($u, 'ipad')) return 'iOS';
    if (str_contains($u, 'linux')) return 'Linux';
    return null;
}

function validarInputUsuario(array $input): array
{
    $nombre = trim((string)($input['nombre'] ?? ''));
    $email  = normalizarEmail((string)($input['email'] ?? ''));

    if ($nombre === '') throw new InvalidArgumentException('BAD_NOMBRE');
    if ($email === '' || !validarEmail($email)) throw new InvalidArgumentException('BAD_EMAIL');

    $tipoRol = (string)($input['tipo_rol'] ?? 'cliente');
    if (!in_array($tipoRol, USUARIOS_ROL_ALLOWED, true)) throw new InvalidArgumentException('BAD_TIPO_ROL');

    $locale = (string)($input['locale'] ?? 'es');
    if (!in_array($locale, USUARIOS_LOCALE_ALLOWED, true)) throw new InvalidArgumentException('BAD_LOCALE');

    // Password opcional (si llega y no está vacío => actualizar)
    $plainPass = trim((string)($input['password'] ?? ''));
    $passwordPlain = null;
    if ($plainPass !== '') {
        if (mb_strlen($plainPass) < 8) throw new InvalidArgumentException('BAD_PASSWORD');
        $passwordPlain = $plainPass;
    }

    return [
        'nombre' => $nombre,
        'email'  => $email,
        'tipo_rol' => $tipoRol,
        'locale'  => $locale,
        'passwordPlain' => $passwordPlain,

        'empresa' => opt($input,'empresa'),
        'nif' => opt($input,'nif'),
        'direccion' => opt($input,'direccion'),
        'ciudad' => opt($input,'ciudad'),
        'codigo_postal' => opt($input,'codigo_postal'),
        'pais' => opt($input,'pais'),
        'telefono' => opt($input,'telefono'),
        'anualitat'=> opt($input,'anualitat'),
    ];
}

/* =========================
   Router PUT por type
========================= */
$type = (string)($_GET['type'] ?? '');

try {

    // =========================================================
    // type=usuarios-update  (PUT)
    // =========================================================
    if ($type === 'usuarios-update') {
        $input = readJsonBody(true);

        $uuidStr = trim((string)($input['uuid'] ?? ''));
        if ($uuidStr === '') throw new InvalidArgumentException('BAD_UUID');
        $uuidBin = uuid_bin_from_string($uuidStr);

        // existe?
        $st = $conn->prepare("SELECT 1 FROM usuarios WHERE uuid = :uuid LIMIT 1");
        $st->execute([':uuid' => $uuidBin]);
        if (!$st->fetchColumn()) {
            jsonResponse(vp2_err('Usuario no encontrado', 'NOT_FOUND'), 404);
        }

        $data = validarInputUsuario($input);

        // Email único excluyendo el propio
        $st = $conn->prepare("
            SELECT 1 FROM usuarios
            WHERE LOWER(email)=LOWER(:email)
              AND uuid <> :uuid
            LIMIT 1
        ");
        $st->execute([
            ':email' => $data['email'],
            ':uuid'  => $uuidBin,
        ]);
        if ($st->fetchColumn()) {
            jsonResponse(vp2_err('Ya existe un usuario con este email', 'EMAIL_EXISTS'), 409);
        }

        // Password solo si viene con valor
        $setPasswordSql = '';
        $passwordParam  = [];
        if ($data['passwordPlain'] !== null) {
            $setPasswordSql = ", password = :password";
            $passwordParam[':password'] = password_hash($data['passwordPlain'], PASSWORD_DEFAULT);
        }

        $ua = userAgent();
        $ip = clientIp();

        $stmt = $conn->prepare("
            UPDATE usuarios
            SET
                nombre = :nombre,
                email = :email,
                estado = :estado,
                empresa = :empresa,
                nif = :nif,
                direccion = :direccion,
                ciudad = :ciudad,
                codigo_postal = :codigo_postal,
                pais = :pais,
                telefono = :telefono,
                anualitat = :anualitat,
                tipo_rol = :tipo_rol,
                locale = :locale,
                dispositiu = :dispositiu,
                navegador = :navegador,
                sistema_operatiu = :sistema_operatiu,
                ip = :ip
                $setPasswordSql
            WHERE uuid = :uuid
            LIMIT 1
        ");

        $params = [
            ':uuid' => $uuidBin,
            ':nombre' => $data['nombre'],
            ':email' => $data['email'],
            ':estado' => ESTADO_FORZADO_INTRANET,

            ':empresa' => $data['empresa'],
            ':nif' => $data['nif'],
            ':direccion' => $data['direccion'],
            ':ciudad' => $data['ciudad'],
            ':codigo_postal' => $data['codigo_postal'],
            ':pais' => $data['pais'],

            ':telefono' => $data['telefono'],
            ':anualitat'=> $data['anualitat'],

            ':tipo_rol' => $data['tipo_rol'],
            ':locale' => $data['locale'],

            ':dispositiu' => detectarDispositivo($ua),
            ':navegador' => detectarNavegador($ua),
            ':sistema_operatiu' => detectarSistemaOperativo($ua),
            ':ip' => $ip,
        ] + $passwordParam;

        $stmt->execute($params);

        jsonResponse(vp2_ok('Usuario actualizado correctamente', [
            'uuid' => $uuidStr,
            'changed' => ($stmt->rowCount() === 1),
        ]), 200);
    }

    // =========================================================
    // type=... (otros endpoints PUT futuros)
    // =========================================================
    jsonResponse(vp2_err('type inválido', 'BAD_TYPE', [
        'allowed' => ['usuarios-update'],
    ]), 400);

} catch (InvalidArgumentException $e) {
    $code = $e->getMessage();

    if ($code === 'BAD_UUID')     jsonResponse(vp2_err('UUID inválido', 'BAD_UUID'), 400);
    if ($code === 'BAD_NOMBRE')   jsonResponse(vp2_err('Nombre inválido', 'BAD_NOMBRE'), 400);
    if ($code === 'BAD_EMAIL')    jsonResponse(vp2_err('Email inválido', 'BAD_EMAIL'), 400);
    if ($code === 'BAD_PASSWORD') jsonResponse(vp2_err('Contraseña inválida (mínimo 8)', 'BAD_PASSWORD'), 400);
    if ($code === 'BAD_TIPO_ROL') jsonResponse(vp2_err('tipo_rol inválido','BAD_TIPO_ROL',['allowed'=>USUARIOS_ROL_ALLOWED]),400);
    if ($code === 'BAD_LOCALE')   jsonResponse(vp2_err('locale inválido','BAD_LOCALE',['allowed'=>USUARIOS_LOCALE_ALLOWED]),400);

    jsonResponse(vp2_err('Parámetros inválidos', 'BAD_PARAM'), 400);

} catch (Throwable $e) {
    jsonResponse(vp2_err('Error del servidor', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
