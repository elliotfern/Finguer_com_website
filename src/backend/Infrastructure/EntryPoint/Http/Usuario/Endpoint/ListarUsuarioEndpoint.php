<?php
declare(strict_types=1);

use App\Infrastructure\EntryPoint\Http\Usuario\Controller\ListarUsuariosController;
use App\Infrastructure\EntryPoint\Http\Usuario\Controller\ObtenerUsuarioController;

// Solo admin (por ahora)
$user = auth_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    jsonResponse(vp2_err('No autoritzat', 'FORBIDDEN'), 403);
}

$type = (string) ($_GET['type'] ?? '');

if ($type === 'usuarios-list') {
    ListarUsuariosController::handle();
    exit();
}

if ($type === 'usuarios-get') {
    ObtenerUsuarioController::handle();
    exit();
}
