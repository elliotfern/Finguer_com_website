<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Usuario\UseCase;

use App\Application\Usuario\UseCase\ListarUsuariosUseCase;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\UsuarioListado;
use App\Domain\Usuario\ValueObjects\UsuarioListCriteria;
use App\Domain\Usuario\ValueObjects\UsuarioResumen;
use PHPUnit\Framework\TestCase;

final class ListarUsuariosUseCaseTest extends TestCase
{
    public function test_execute_mapea_listado_de_dominio_a_dto_de_aplicacion(): void
    {
        $criteria = UsuarioListCriteria::fromRequest([
            'q' => 'maria',
            'role' => 'cliente_anual',
        ]);

        $uuid = UsuarioUuid::generate();
        $createdAt = new \DateTimeImmutable('2026-07-01 10:00:00');

        $resumen = new UsuarioResumen(
            uuid: $uuid,
            nombre: 'Maria Garcia',
            email: 'maria@example.com',
            telefono: '600111222',
            rol: Rol::ClienteAnual,
            createdAt: $createdAt,
        );

        $listado = new UsuarioListado([$resumen], 1);

        $repository = $this->createMock(UsuarioRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($criteria)
            ->willReturn($listado);

        $useCase = new ListarUsuariosUseCase($repository);
        $result = $useCase->execute($criteria);

        $this->assertSame(1, $result->total);
        $this->assertCount(1, $result->items);

        $item = $result->items[0];
        $this->assertSame($uuid->toString(), $item->uuid);
        $this->assertSame('Maria Garcia', $item->nombre);
        $this->assertSame('maria@example.com', $item->email);
        $this->assertSame('600111222', $item->telefono);
        $this->assertSame('cliente_anual', $item->tipoRol);
        $this->assertSame('2026-07-01 10:00:00', $item->createdAt);
    }

    public function test_execute_devuelve_created_at_null_cuando_resumen_no_lo_tiene(): void
    {
        $criteria = UsuarioListCriteria::fromRequest([]);
        $uuid = UsuarioUuid::generate();

        $resumen = new UsuarioResumen(
            uuid: $uuid,
            nombre: 'Test',
            email: 'test@example.com',
            telefono: '',
            rol: Rol::Cliente,
            createdAt: null,
        );

        $listado = new UsuarioListado([$resumen], 1);

        $repository = $this->createStub(UsuarioRepositoryInterface::class);
        $repository->method('findByCriteria')->willReturn($listado);

        $useCase = new ListarUsuariosUseCase($repository);
        $result = $useCase->execute($criteria);

        $this->assertNull($result->items[0]->createdAt);
    }

    public function test_execute_returns_empty_result_when_repository_finds_nothing(): void
    {
        $criteria = UsuarioListCriteria::fromRequest([]);
        $emptyListado = new UsuarioListado([], 0);

        $repository = $this->createStub(UsuarioRepositoryInterface::class);
        $repository->method('findByCriteria')->willReturn($emptyListado);

        $useCase = new ListarUsuariosUseCase($repository);
        $result = $useCase->execute($criteria);

        $this->assertSame(0, $result->total);
        $this->assertSame([], $result->items);
    }
}
