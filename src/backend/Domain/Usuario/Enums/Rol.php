<?php
// Rol.php
declare(strict_types=1);
namespace App\Domain\Usuario\Enums;

enum Rol: string
{
    case Cliente = 'cliente';
    case ClienteAnual = 'cliente_anual';
    case Admin = 'admin';
    case Trabajador = 'trabajador';
}
