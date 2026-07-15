<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Reserva\ValueObjects;

use App\Domain\Reserva\ValueObjects\ReservaServicioLinea;
use PHPUnit\Framework\TestCase;

final class ReservaServicioLineaTest extends TestCase
{
    public function test_almacena_todos_los_campos_correctamente(): void
    {
        $linea = new ReservaServicioLinea(
            servicioId: 1,
            descripcion: 'Reserva Finguer Class',
            cantidad: 1.0,
            precioUnitario: 100.0,
            impuestoPercent: 21.0,
            totalBase: 100.0,
            totalImpuesto: 21.0,
            totalLinea: 121.0,
        );

        $this->assertSame(1, $linea->servicioId);
        $this->assertSame('Reserva Finguer Class', $linea->descripcion);
        $this->assertSame(1.0, $linea->cantidad);
        $this->assertSame(100.0, $linea->precioUnitario);
        $this->assertSame(21.0, $linea->impuestoPercent);
        $this->assertSame(100.0, $linea->totalBase);
        $this->assertSame(21.0, $linea->totalImpuesto);
        $this->assertSame(121.0, $linea->totalLinea);
    }
}
