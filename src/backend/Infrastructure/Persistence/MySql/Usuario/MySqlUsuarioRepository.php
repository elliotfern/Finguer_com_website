<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql\Usuario;

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Perfil;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\DireccionPostal;
use App\Domain\Usuario\ValueObjects\Nif;
use App\Domain\Usuario\ValueObjects\NombreCompleto;
use App\Domain\Usuario\ValueObjects\Telefono;
use PDO;

final class MySqlUsuarioRepository implements UsuarioRepositoryInterface
{
    public function __construct(private readonly PDO $conn) {}

    public function findByUuid(UsuarioUuid $uuid): ?Usuario
    {
        $stmt = $this->conn->prepare("
            SELECT uuid, email, estado, password, tipo_rol, locale
            FROM usuarios
            WHERE uuid = :uuid
            LIMIT 1
        ");
        $stmt->bindValue(':uuid', $uuid->toBytes(), PDO::PARAM_LOB);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToUsuario($row) : null;
    }

    public function findByEmail(Email $email): ?Usuario
    {
        $stmt = $this->conn->prepare("
            SELECT uuid, email, estado, password, tipo_rol, locale
            FROM usuarios
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->bindValue(':email', $email->value());
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToUsuario($row) : null;
    }

    public function findPerfilByUuid(UsuarioUuid $uuid): ?Perfil
    {
        $stmt = $this->conn->prepare("
            SELECT nombre, telefono, empresa, nif,
                   direccion, ciudad, codigo_postal, pais
            FROM usuarios_perfil
            WHERE usuario_uuid = :uuid
            LIMIT 1
        ");
        $stmt->bindValue(':uuid', $uuid->toBytes(), PDO::PARAM_LOB);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToPerfil($uuid, $row) : null;
    }

    public function save(Usuario $usuario): void
    {
        $stmt = $this->conn->prepare("
            INSERT INTO usuarios (uuid, email, estado, password, tipo_rol, locale)
            VALUES (:uuid, :email, :estado, :password, :tipo_rol, :locale)
            ON DUPLICATE KEY UPDATE
                email    = VALUES(email),
                estado   = VALUES(estado),
                password = VALUES(password),
                tipo_rol = VALUES(tipo_rol),
                locale   = VALUES(locale)
        ");
        $stmt->execute([
            ':uuid' => $usuario->uuid()->toBytes(),
            ':email' => $usuario->email()->value(),
            ':estado' => $usuario->estado()->value,
            ':password' => null,
            ':tipo_rol' => $usuario->rol()->value,
            ':locale' => $usuario->locale()->value,
        ]);
    }

    public function savePerfil(Perfil $perfil): void
    {
        $dir = $perfil->direccion();
        $stmt = $this->conn->prepare("
            INSERT INTO usuarios_perfil
                (usuario_uuid, nombre, telefono, empresa, nif, direccion, ciudad, codigo_postal, pais)
            VALUES
                (:uuid, :nombre, :telefono, :empresa, :nif, :direccion, :ciudad, :codigo_postal, :pais)
            ON DUPLICATE KEY UPDATE
                nombre        = VALUES(nombre),
                telefono      = VALUES(telefono),
                empresa       = VALUES(empresa),
                nif           = VALUES(nif),
                direccion     = VALUES(direccion),
                ciudad        = VALUES(ciudad),
                codigo_postal = VALUES(codigo_postal),
                pais          = VALUES(pais)
        ");
        $stmt->execute([
            ':uuid' => $perfil->usuarioUuid()->toBytes(),
            ':nombre' => $perfil->nombre()->value(),
            ':telefono' => $perfil->telefono()?->value(),
            ':empresa' => $perfil->empresa(),
            ':nif' => $perfil->nif()?->value(),
            ':direccion' => $dir->direccion(),
            ':ciudad' => $dir->ciudad(),
            ':codigo_postal' => $dir->codigoPostal(),
            ':pais' => $dir->pais(),
        ]);
    }

    public function existsEmail(Email $email): bool
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) FROM usuarios WHERE email = :email
        ");
        $stmt->bindValue(':email', $email->value());
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    // ── Mappers ──────────────────────────────────────────────

    private function mapToUsuario(array $row): Usuario
    {
        return Usuario::fromDatabase(
            uuid: UsuarioUuid::fromBytes($row['uuid']),
            email: Email::fromString($row['email']),
            estado: UsuarioEstado::from($row['estado']),
            rol: Rol::from($row['tipo_rol']),
            locale: Locale::from($row['locale']),
            password: $row['password'],
        );
    }

    private function mapToPerfil(UsuarioUuid $uuid, array $row): Perfil
    {
        return Perfil::fromDatabase(
            usuarioUuid: $uuid,
            nombre: NombreCompleto::fromString($row['nombre']),
            telefono: $row['telefono']
                ? Telefono::fromString($row['telefono'])
                : null,
            nif: $row['nif'] ? Nif::fromString($row['nif']) : null,
            empresa: $row['empresa'],
            direccion: DireccionPostal::create(
                $row['direccion'],
                $row['ciudad'],
                $row['codigo_postal'],
                $row['pais'],
            ),
        );
    }
}
