<?php
// Locale.php
declare(strict_types=1);
namespace App\Domain\Usuario\Enums;

enum Locale: string
{
    case Ca = 'ca';
    case Es = 'es';
    case Fr = 'fr';
    case En = 'en';
    case It = 'it';

    public static function default(): self
    {
        return self::Es;
    }
}
