<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Catalogo\Controller;

use App\Application\Catalogo\UseCase\ObtenerConfiguracionReserva;

final class ConfiguracionReservaController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit();
        }

        $useCase = new ObtenerConfiguracionReserva();
        $config = $useCase->execute();

        echo json_encode(
            [
                'ok' => true,
                'fechaMinima' => $config['fecha_minima'],
                'fechaMaxima' => $config['fecha_maxima'],
                'fechasNoDisponibles' => $config['fechas_no_disponibles'],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
