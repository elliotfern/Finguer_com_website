<?php
// tests/Domain/Shared/EmailTest.php

declare(strict_types=1);

use App\Domain\Shared\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_email_valido(): void
    {
        $email = Email::fromString('test@example.com');
        $this->assertSame('test@example.com', $email->value());
    }

    public function test_email_se_normaliza_a_minusculas(): void
    {
        $email = Email::fromString('TEST@EXAMPLE.COM');
        $this->assertSame('test@example.com', $email->value());
    }

    public function test_email_vacio_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString('');
    }

    public function test_email_invalido_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString('no-es-un-email');
    }

    public function test_dos_emails_iguales(): void
    {
        $a = Email::fromString('test@example.com');
        $b = Email::fromString('TEST@example.com');
        $this->assertTrue($a->equals($b));
    }
}
