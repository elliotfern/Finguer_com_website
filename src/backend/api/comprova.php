<?php
header('Content-Type: application/json');

$has = !empty($_COOKIE['token']);
$out = ['has_cookie' => $has];

if (!$has) {
  echo json_encode($out);
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

echo json_encode($out);
