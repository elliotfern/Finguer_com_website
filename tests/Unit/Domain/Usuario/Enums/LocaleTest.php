<?php
// LocaleTest.php
declare(strict_types=1);

use App\Domain\Usuario\Enums\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    public function test_desde_string_valido(): void
    {
        $locale = Locale::from('ca');
        $this->assertSame(Locale::Ca, $locale);
    }

    public function test_locale_por_defecto(): void
    {
        $this->assertSame(Locale::Es, Locale::default());
    }

    public function test_string_invalido_lanza_excepcion(): void
    {
        $this->expectException(\ValueError::class);
        Locale::from('invalido');
    }
}
