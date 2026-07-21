<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reserva\UseCase;

use App\Application\Reserva\UseCase\CrearReservaAnualUseCase;
use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\Service\LocalizadorGeneratorInterface;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CrearReservaAnualUseCaseTest extends TestCase
{
    private function makeUsuario(
        UsuarioUuid $uuid,
        UsuarioEstado $estado = UsuarioEstado::Activo,
    ): Usuario {
        return Usuario::fromDatabase(
            $uuid,
            Email::fromString('test@finguer.com'),
            $estado,
            Rol::ClienteAnual,
            Locale::Es,
            null,
        );
    }

    private function baseInput(string $usuarioUuid): array
    {
        return [
            'usuario_uuid' => $usuarioUuid,
            'diaEntrada' => '2026-08-07',
            'horaEntrada' => '10:00',
            'diaSalida' => null,
            'horaSalida' => null,
            'vehiculo' => 'Seat Ibiza',
            'matricula' => '1234ABC',
            'vuelo' => null,
            'notes' => 'Cliente VIP',
        ];
    }

    public function test_crea_reserva_anual_correctamente(): void
    {
        $uuid = UsuarioUuid::generate();
        $usuario = $this->makeUsuario($uuid);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );
        $localizadorGen->method('generar')->willReturn('0708269999');

        $reservaRepo = $this->createMock(ReservaRepositoryInterface::class);
        $reservaRepo
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn(Reserva $r) => $r->conId(1));

        $useCase = new CrearReservaAnualUseCase(
            $usuarioRepo,
            $reservaRepo,
            $localizadorGen,
        );
        $reserva = $useCase->execute($this->baseInput($uuid->toString()));

        $this->assertSame(1, $reserva->id());
        $this->assertSame('0708269999', $reserva->localizador());
        $this->assertNull($reserva->totalCalculado());
    }

    public function test_permite_usuario_no_activo(): void
    {
        $uuid = UsuarioUuid::generate();
        $usuario = $this->makeUsuario($uuid, UsuarioEstado::Bloqueado);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );
        $localizadorGen->method('generar')->willReturn('0708269999');

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $reservaRepo
            ->method('save')
            ->willReturnCallback(fn(Reserva $r) => $r->conId(1));

        $useCase = new CrearReservaAnualUseCase(
            $usuarioRepo,
            $reservaRepo,
            $localizadorGen,
        );
        $reserva = $useCase->execute($this->baseInput($uuid->toString()));

        $this->assertNotNull($reserva->id());
    }

    public function test_lanza_excepcion_si_usuario_no_existe(): void
    {
        $uuid = UsuarioUuid::generate();

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn(null);

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );

        $useCase = new CrearReservaAnualUseCase(
            $usuarioRepo,
            $reservaRepo,
            $localizadorGen,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('USUARIO_NO_ENCONTRADO');

        $useCase->execute($this->baseInput($uuid->toString()));
    }

    public function test_lanza_excepcion_si_fecha_entrada_invalida(): void
    {
        $uuid = UsuarioUuid::generate();
        $usuario = $this->makeUsuario($uuid);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );

        $useCase = new CrearReservaAnualUseCase(
            $usuarioRepo,
            $reservaRepo,
            $localizadorGen,
        );

        $input = $this->baseInput($uuid->toString());
        $input['diaEntrada'] = 'fecha-invalida';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ENTRADA_INVALIDA');

        $useCase->execute($input);
    }

    public function test_lanza_excepcion_si_salida_es_anterior_a_entrada(): void
    {
        $uuid = UsuarioUuid::generate();
        $usuario = $this->makeUsuario($uuid);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );

        $useCase = new CrearReservaAnualUseCase(
            $usuarioRepo,
            $reservaRepo,
            $localizadorGen,
        );

        $input = $this->baseInput($uuid->toString());
        $input['diaSalida'] = '2026-08-01';
        $input['horaSalida'] = '10:00';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SALIDA_ANTERIOR_A_ENTRADA');

        $useCase->execute($input);
    }

    public function test_acepta_salida_valida_posterior_a_entrada(): void
    {
        $uuid = UsuarioUuid::generate();
        $usuario = $this->makeUsuario($uuid);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );
        $localizadorGen->method('generar')->willReturn('0708269999');

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $reservaRepo
            ->method('save')
            ->willReturnCallback(fn(Reserva $r) => $r->conId(1));

        $useCase = new CrearReservaAnualUseCase(
            $usuarioRepo,
            $reservaRepo,
            $localizadorGen,
        );

        $input = $this->baseInput($uuid->toString());
        $input['diaSalida'] = '2027-08-01';
        $input['horaSalida'] = '10:00';

        $reserva = $useCase->execute($input);

        $this->assertSame(
            '2027-08-01 10:00:00',
            $reserva->salidaPrevista()->format('Y-m-d H:i:s'),
        );
    }
}
