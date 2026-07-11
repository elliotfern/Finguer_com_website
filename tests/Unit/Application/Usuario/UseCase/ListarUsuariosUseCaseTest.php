<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Usuario\UseCase;

use App\Application\Usuario\DTO\UsuarioListItemDTO;
use App\Application\Usuario\DTO\UsuarioListResult;
use App\Application\Usuario\UseCase\ListarUsuariosUseCase;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\UsuarioListCriteria;
use PHPUnit\Framework\TestCase;

final class ListarUsuariosUseCaseTest extends TestCase
{
    public function test_execute_delegates_to_repository_with_same_criteria(): void
    {
        $criteria = UsuarioListCriteria::fromRequest([
            'q' => 'maria',
            'role' => 'cliente_anual',
        ]);

        $expectedResult = new UsuarioListResult(
            items: [
                new UsuarioListItemDTO(
                    uuid: '018f2e2e-1234-7000-8000-abcdefabcdef',
                    nombre: 'Maria Garcia',
                    email: 'maria@example.com',
                    telefono: '600111222',
                    tipoRol: Rol::ClienteAnual->value,
                    createdAt: '2026-07-01 10:00:00',
                ),
            ],
            total: 1,
        );

        $repository = $this->createMock(UsuarioRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findByCriteria')
            ->with($criteria)
            ->willReturn($expectedResult);

        $useCase = new ListarUsuariosUseCase($repository);
        $result = $useCase->execute($criteria);

        $this->assertSame($expectedResult, $result);
    }

    public function test_execute_returns_empty_result_when_repository_finds_nothing(): void
    {
        $criteria = UsuarioListCriteria::fromRequest([]);
        $emptyResult = new UsuarioListResult([], 0);

        $repository = $this->createStub(UsuarioRepositoryInterface::class);
        $repository->method('findByCriteria')->willReturn($emptyResult);

        $useCase = new ListarUsuariosUseCase($repository);
        $result = $useCase->execute($criteria);

        $this->assertSame(0, $result->total);
        $this->assertSame([], $result->items);
    }
}
