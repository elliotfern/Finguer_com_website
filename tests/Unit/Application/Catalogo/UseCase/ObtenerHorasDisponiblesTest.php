<?php

declare(strict_types=1);

use App\Application\Catalogo\DTO\ObtenerHorasDisponiblesDTO;
use App\Application\Catalogo\UseCase\ObtenerHorasDisponibles;
use PHPUnit\Framework\TestCase;

class ObtenerHorasDisponiblesTest extends TestCase
{
    private ObtenerHorasDisponibles $useCase;

    protected function setUp(): void
    {
        $this->useCase = new ObtenerHorasDisponibles();
    }

    public function test_horas_finguer_class(): void
    {
        $dto = new ObtenerHorasDisponiblesDTO('RESERVA_FINGUER', '2026-06-15');
        $horas = $this->useCase->execute($dto);

        $this->assertContains('07:00', $horas);
        $this->assertContains('23:30', $horas);
        $this->assertNotEmpty($horas);
    }

    public function test_horas_gold_class(): void
    {
        $dto = new ObtenerHorasDisponiblesDTO(
            'RESERVA_FINGUER_GOLD',
            '2026-06-15',
        );
        $horas = $this->useCase->execute($dto);

        $this->assertContains('08:00', $horas);
        $this->assertContains('21:00', $horas);
        $this->assertNotContains('07:00', $horas);
        $this->assertNotContains('23:30', $horas);
    }

    public function test_horas_reducidas_en_nochebuena(): void
    {
        $dto = new ObtenerHorasDisponiblesDTO('RESERVA_FINGUER', '2026-12-24');
        $horas = $this->useCase->execute($dto);

        $this->assertContains('07:00', $horas);
        $this->assertContains('18:00', $horas);
        $this->assertNotContains('18:30', $horas);
        $this->assertNotContains('23:30', $horas);
    }
}
