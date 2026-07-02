<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Catalogo;

use App\Application\Catalogo\DTO\ObtenerHorasDisponiblesDTO;
use App\Application\Catalogo\UseCase\ObtenerHorasDisponibles;
use App\Domain\Catalogo\Rules\HorariosReserva;
use App\Domain\Catalogo\Rules\ReglasReserva;

final class HorasDisponiblesController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit();
        }

        $tipoReserva = strtoupper(trim((string) ($_GET['tipoReserva'] ?? '')));
        $fechaStr = trim((string) ($_GET['fecha'] ?? ''));

        if ($tipoReserva === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Missing tipoReserva']);
            exit();
        }

        if (
            !in_array(
                $tipoReserva,
                [
                    HorariosReserva::TIPO_FINGUER_CLASS,
                    HorariosReserva::TIPO_GOLD_CLASS,
                ],
                true,
            )
        ) {
            http_response_code(400);
            echo json_encode([
                'error' => "Tipo de reserva no válido: {$tipoReserva}",
            ]);
            exit();
        }

        if ($fechaStr === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Missing fecha']);
            exit();
        }

        try {
            $fecha = new \DateTimeImmutable(
                $fechaStr,
                new \DateTimeZone(ReglasReserva::TIMEZONE),
            );
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid fecha format. Use "YYYY-MM-DD"',
            ]);
            exit();
        }

        $dto = new ObtenerHorasDisponiblesDTO(
            $tipoReserva,
            $fecha->format('Y-m-d'),
        );
        $useCase = new ObtenerHorasDisponibles();
        $horas = $useCase->execute($dto);

        echo json_encode(
            [
                'ok' => true,
                'tipoReserva' => $tipoReserva,
                'fecha' => $fecha->format('Y-m-d'),
                'horas' => $horas,
                'avisoHorarioEspecial' => HorariosReserva::esVisperaNavidad(
                    $fecha,
                ),
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
