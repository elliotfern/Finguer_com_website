<?php
// URL: api/carro-compra/configuracion-reserva

declare(strict_types=1);

use App\utils\Reserva\ReglasReserva;

header('Content-Type: application/json; charset=utf-8');

// Solo lectura: GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

echo json_encode(
    [
        'ok' => true,
        'fechaMinima' => ReglasReserva::fechaMinima(),
        'fechaMaxima' => ReglasReserva::FECHA_MAXIMA,
        'fechasNoDisponibles' => ReglasReserva::fechasNoDisponibles(),
    ],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
);
