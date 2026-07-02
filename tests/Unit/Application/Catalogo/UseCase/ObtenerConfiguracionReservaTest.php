<?php

declare(strict_types=1);

use App\Application\Catalogo\UseCase\ObtenerConfiguracionReserva;
use PHPUnit\Framework\TestCase;

class ObtenerConfiguracionReservaTest extends TestCase
{
    public function test_devuelve_configuracion_completa(): void
    {
        $useCase = new ObtenerConfiguracionReserva();
        $config = $useCase->execute();

        $this->assertArrayHasKey('fecha_minima', $config);
        $this->assertArrayHasKey('fecha_maxima', $config);
        $this->assertArrayHasKey('dias_antelacion_minima', $config);
        $this->assertArrayHasKey('fechas_no_disponibles', $config);
        $this->assertArrayHasKey('horas_finguer_class', $config);
        $this->assertArrayHasKey('horas_gold_class', $config);
    }

    public function test_fecha_maxima_correcta(): void
    {
        $useCase = new ObtenerConfiguracionReserva();
        $config = $useCase->execute();

        $this->assertSame('2027-12-31', $config['fecha_maxima']);
    }
}
