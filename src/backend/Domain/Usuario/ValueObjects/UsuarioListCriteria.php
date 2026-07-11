<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

use App\Domain\Usuario\Enums\Rol;

final class UsuarioListCriteria
{
    private function __construct(
        public readonly int $limit,
        public readonly int $offset,
        public readonly string $q,
        public readonly ?Rol $role,
    ) {}

    public static function fromRequest(array $params): self
    {
        $limit = (int) ($params['limit'] ?? 50);
        $limit = $limit < 1 ? 50 : min($limit, 200);

        $offset = (int) ($params['offset'] ?? 0);
        $offset = max($offset, 0);

        $q = trim((string) ($params['q'] ?? ''));

        $roleParam = trim((string) ($params['role'] ?? ''));
        $role = $roleParam !== '' ? Rol::tryFrom($roleParam) : null;

        return new self($limit, $offset, $q, $role);
    }
}
