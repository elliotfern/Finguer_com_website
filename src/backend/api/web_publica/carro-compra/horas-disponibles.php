<?php
// url:  api/carro-compra/horas-disponibles

declare(strict_types=1);

use App\utils\Reserva\HorariosReserva;
use App\utils\Reserva\ReglasReserva;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

function badRequest(string $msg): void
{
    http_response_code(400);
    echo json_encode(['error' => $msg]);
    exit();
}

$tipoReserva = strtoupper(trim((string) ($_GET['tipoReserva'] ?? '')));
$fechaStr = trim((string) ($_GET['fecha'] ?? ''));

if ($tipoReserva === '') {
    badRequest('Missing tipoReserva');
}

if (
    !in_array(
        $tipoReserva,
        [HorariosReserva::TIPO_FINGUER_CLASS, HorariosReserva::TIPO_GOLD_CLASS],
        true,
    )
) {
    badRequest("Tipo de reserva no válido: {$tipoReserva}");
}

if ($fechaStr === '') {
    badRequest('Missing fecha');
}

try {
    $fecha = new DateTime($fechaStr, new DateTimeZone(ReglasReserva::TIMEZONE));
} catch (Throwable $e) {
    badRequest('Invalid fecha format. Use "YYYY-MM-DD"');
}

echo json_encode(
    [
        'ok' => true,
        'tipoReserva' => $tipoReserva,
        'fecha' => $fecha->format('Y-m-d'),
        'horas' => HorariosReserva::horasDisponibles($tipoReserva, $fecha),
        'avisoHorarioEspecial' => HorariosReserva::esVisperaNavidad($fecha),
    ],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
);
