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
use App\Domain\Usuario\ValueObjects\UsuarioListado;
use App\Domain\Usuario\ValueObjects\UsuarioResumen;
use App\Domain\Usuario\ValueObjects\UsuarioListCriteria;
use DateTimeImmutable;
use PDO;

final class MySqlUsuarioRepository implements UsuarioRepositoryInterface
{
    public function __construct(private readonly PDO $conn) {}

    public function findByUuid(UsuarioUuid $uuid): ?Usuario
    {
        $stmt = $this->conn->prepare("
        SELECT uuid, email, estado, password, tipo_rol, locale, created_at, updated_at
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
        SELECT uuid, email, estado, password, tipo_rol, locale, created_at, updated_at
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
            SELECT nombre, telefono, empresa, nif, direccion, ciudad, codigo_postal, pais
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
        if ($usuario->createdAt() === null) {
            throw new \LogicException(
                'Usuario::createdAt() es null. El objeto Usuario debe crearse con Usuario::create() o reconstruirse con Usuario::fromDatabase() antes de guardar.',
            );
        }

        $stmt = $this->conn->prepare("
        INSERT INTO usuarios (uuid, email, estado, password, tipo_rol, locale, created_at, updated_at)
        VALUES (:uuid, :email, :estado, :password, :tipo_rol, :locale, :created_at, :updated_at)
        ON DUPLICATE KEY UPDATE
            email      = VALUES(email),
            estado     = VALUES(estado),
            password   = VALUES(password),
            tipo_rol   = VALUES(tipo_rol),
            locale     = VALUES(locale),
            updated_at = VALUES(updated_at)
    ");
        $stmt->execute([
            ':uuid' => $usuario->uuid()->toBytes(),
            ':email' => $usuario->email()->value(),
            ':estado' => $usuario->estado()->value,
            ':password' => null,
            ':tipo_rol' => $usuario->rol()->value,
            ':locale' => $usuario->locale()->value,
            ':created_at' => $usuario->createdAt()->format('Y-m-d H:i:s'),
            ':updated_at' =>
                $usuario->updatedAt()?->format('Y-m-d H:i:s') ??
                $usuario->createdAt()->format('Y-m-d H:i:s'),
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

    public function findByCriteria(
        UsuarioListCriteria $criteria,
    ): UsuarioListado {
        $roleWhere = $criteria->role !== null ? ' AND u.tipo_rol = :role ' : '';
        $qLike = '%' . $criteria->q . '%';

        $sql = "
        SELECT
            u.uuid,
            p.nombre,
            u.email,
            p.telefono,
            u.tipo_rol,
            u.created_at
        FROM usuarios u
        LEFT JOIN usuarios_perfil AS p ON u.uuid = p.usuario_uuid
        WHERE 1=1
          AND (:q = '' OR p.nombre LIKE :qLikeNombre OR u.email LIKE :qLikeEmail OR p.telefono LIKE :qLikeTelefono)
          {$roleWhere}
        ORDER BY u.created_at DESC
        LIMIT :limit OFFSET :offset
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':q', $criteria->q, PDO::PARAM_STR);
        $stmt->bindValue(':qLikeNombre', $qLike, PDO::PARAM_STR);
        $stmt->bindValue(':qLikeEmail', $qLike, PDO::PARAM_STR);
        $stmt->bindValue(':qLikeTelefono', $qLike, PDO::PARAM_STR);
        if ($criteria->role !== null) {
            $stmt->bindValue(':role', $criteria->role->value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $criteria->limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $criteria->offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlCount = "
        SELECT COUNT(*) AS total
        FROM usuarios u
        LEFT JOIN usuarios_perfil AS p ON u.uuid = p.usuario_uuid
        WHERE 1=1
          AND (:q = '' OR p.nombre LIKE :qLikeNombre OR u.email LIKE :qLikeEmail OR p.telefono LIKE :qLikeTelefono)
          {$roleWhere}
    ";

        $stmtC = $this->conn->prepare($sqlCount);
        $stmtC->bindValue(':q', $criteria->q, PDO::PARAM_STR);
        $stmtC->bindValue(':qLikeNombre', $qLike, PDO::PARAM_STR);
        $stmtC->bindValue(':qLikeEmail', $qLike, PDO::PARAM_STR);
        $stmtC->bindValue(':qLikeTelefono', $qLike, PDO::PARAM_STR);
        if ($criteria->role !== null) {
            $stmtC->bindValue(':role', $criteria->role->value, PDO::PARAM_STR);
        }
        $stmtC->execute();
        $total = (int) ($stmtC->fetchColumn() ?: 0);

        $items = array_map(
            fn(array $row) => new UsuarioResumen(
                uuid: UsuarioUuid::fromBytes($row['uuid']),
                nombre: (string) ($row['nombre'] ?? ''),
                email: (string) ($row['email'] ?? ''),
                telefono: (string) ($row['telefono'] ?? ''),
                rol: Rol::from($row['tipo_rol']),
                createdAt: $row['created_at']
                    ? new \DateTimeImmutable($row['created_at'])
                    : null,
            ),
            $rows,
        );

        return new UsuarioListado($items, $total);
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
            createdAt: isset($row['created_at'])
                ? new DateTimeImmutable($row['created_at'])
                : null,
            updatedAt: isset($row['updated_at'])
                ? new DateTimeImmutable($row['updated_at'])
                : null,
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
