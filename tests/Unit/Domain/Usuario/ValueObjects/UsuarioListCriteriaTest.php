<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Usuario\ValueObject;

use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\ValueObjects\UsuarioListCriteria;
use PHPUnit\Framework\TestCase;

final class UsuarioListCriteriaTest extends TestCase
{
    public function test_defaults_when_no_params(): void
    {
        $criteria = UsuarioListCriteria::fromRequest([]);

        $this->assertSame(50, $criteria->limit);
        $this->assertSame(0, $criteria->offset);
        $this->assertSame('', $criteria->q);
        $this->assertNull($criteria->role);
    }

    public function test_limit_is_clamped_to_max_200(): void
    {
        $criteria = UsuarioListCriteria::fromRequest(['limit' => 500]);
        $this->assertSame(200, $criteria->limit);
    }

    public function test_limit_below_one_falls_back_to_default(): void
    {
        $criteria = UsuarioListCriteria::fromRequest(['limit' => 0]);
        $this->assertSame(50, $criteria->limit);

        $criteria = UsuarioListCriteria::fromRequest(['limit' => -10]);
        $this->assertSame(50, $criteria->limit);
    }

    public function test_offset_cannot_be_negative(): void
    {
        $criteria = UsuarioListCriteria::fromRequest(['offset' => -5]);
        $this->assertSame(0, $criteria->offset);
    }

    public function test_q_is_trimmed(): void
    {
        $criteria = UsuarioListCriteria::fromRequest(['q' => '  maria  ']);
        $this->assertSame('maria', $criteria->q);
    }

    public function test_valid_role_is_parsed_to_enum(): void
    {
        $criteria = UsuarioListCriteria::fromRequest([
            'role' => 'cliente_anual',
        ]);
        $this->assertSame(Rol::ClienteAnual, $criteria->role);
    }

    public function test_invalid_role_falls_back_to_null(): void
    {
        $criteria = UsuarioListCriteria::fromRequest(['role' => 'superadmin']);
        $this->assertNull($criteria->role);
    }

    public function test_empty_role_is_null(): void
    {
        $criteria = UsuarioListCriteria::fromRequest(['role' => '']);
        $this->assertNull($criteria->role);
    }
}
