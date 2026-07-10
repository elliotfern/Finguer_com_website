<?php
declare(strict_types=1);

use App\Infrastructure\EntryPoint\Http\Usuario\ActualizarUsuarioController;
use App\Infrastructure\EntryPoint\Http\Usuario\ActualizarClienteAnualController;

$type = (string) ($_GET['type'] ?? '');

//
if ($type === 'usuarios-update') {
    ActualizarUsuarioController::handle();
    exit();
}

// PUT -> Actualitza dades client anual
// RUTA: /api/usuaris/put?type=clienteAnual-update
if ($type === 'clienteAnual-update') {
    ActualizarClienteAnualController::handle();
    exit();
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'type inválido']);
