<?php
// UsuarioEstado.php
declare(strict_types=1);
namespace App\Domain\Usuario\Enums;

enum UsuarioEstado: string
{
    case Pendiente = 'pendiente';
    case Activo = 'activo';
    case Bloqueado = 'bloqueado';
    case Eliminado = 'eliminado';
}
