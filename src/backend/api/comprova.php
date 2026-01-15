<?php
header('Content-Type: application/json; charset=utf-8');

$has = !empty($_COOKIE['token']);
$out = [
  'has_cookie' => $has,

  // contexto de la petición
  'host'  => $_SERVER['HTTP_HOST'] ?? '',
  'https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? true : false,
  'uri'   => $_SERVER['REQUEST_URI'] ?? '',

  // si el navegador está enviando cookies (header Cookie existe)
  'cookie_hdr'     => isset($_SERVER['HTTP_COOKIE']),
  'cookie_hdr_len' => isset($_SERVER['HTTP_COOKIE']) ? strlen((string)$_SERVER['HTTP_COOKIE']) : 0,
];

if (!$has) {
  echo json_encode($out, JSON_UNESCAPED_SLASHES);
  exit;
}

$jwt = (string)$_COOKIE['token'];
$out['cookie_len'] = strlen($jwt);

$payload = validarToken($jwt);
$out['valid'] = $payload !== false;

if ($payload === false) {
  $out['error'] = 'invalid_or_expired';
} else {
  $out['sub'] = (string)$payload->sub;
  $out['role'] = (string)$payload->role;
  $out['exp'] = (int)($payload->exp ?? 0);
}

echo json_encode($out, JSON_UNESCAPED_SLASHES);
