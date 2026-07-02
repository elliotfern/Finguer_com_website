<?php
declare(strict_types=1);

use App\Infrastructure\EntryPoint\Http\Usuario\CrearUsuarioController;
use App\Infrastructure\EntryPoint\Http\Usuario\CrearClienteAnualController;

$type = (string) ($_GET['type'] ?? '');

if ($type === 'usuarios-create') {
    CrearUsuarioController::handle();
    exit();
}
if ($type === 'clienteAnual-create') {
    CrearClienteAnualController::handle();
    exit();
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'type inválido']);
