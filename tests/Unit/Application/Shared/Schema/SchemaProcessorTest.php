<?php

declare(strict_types=1);

use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Shared\Schema\SchemaValidationException;
use PHPUnit\Framework\TestCase;

class SchemaProcessorTest extends TestCase
{
    private function schema(): array
    {
        return [
            'nombre' => [
                'rules' => 'required|string|max:255',
                'label' => 'Nombre',
            ],
            'email' => ['rules' => 'required|email', 'label' => 'Email'],
            'edad' => [
                'rules' => 'int|min_value:0|max_value:120',
                'label' => 'Edad',
            ],
        ];
    }

    public function test_procesa_input_valido_sin_lanzar_excepcion(): void
    {
        $result = SchemaProcessor::process(
            ['nombre' => 'Maria', 'email' => 'maria@example.com', 'edad' => 30],
            $this->schema(),
        );

        $this->assertSame('Maria', $result['nombre']);
        $this->assertSame('maria@example.com', $result['email']);
        $this->assertSame(30, $result['edad']);
    }

    public function test_acumula_todos_los_errores_de_una_vez(): void
    {
        try {
            SchemaProcessor::process(
                ['nombre' => '', 'email' => 'no-valido', 'edad' => 30],
                $this->schema(),
            );
            $this->fail('Se esperaba SchemaValidationException');
        } catch (SchemaValidationException $e) {
            $errors = $e->toApiArray();

            $this->assertArrayHasKey('nombre', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayNotHasKey('edad', $errors);
        }
    }

    public function test_error_incluye_label_y_messages(): void
    {
        try {
            SchemaProcessor::process(['email' => 'invalido'], $this->schema());
            $this->fail('Se esperaba SchemaValidationException');
        } catch (SchemaValidationException $e) {
            $errors = $e->toApiArray();

            $this->assertSame('Nombre', $errors['nombre']['label']);
            $this->assertIsArray($errors['nombre']['messages']);
        }
    }

    public function test_normaliza_strings_con_trim(): void
    {
        $result = SchemaProcessor::process(
            ['nombre' => '  Maria  ', 'email' => 'maria@example.com'],
            $this->schema(),
        );

        $this->assertSame('Maria', $result['nombre']);
    }

    public function test_campo_opcional_ausente_se_guarda_como_null(): void
    {
        $result = SchemaProcessor::process(
            ['nombre' => 'Maria', 'email' => 'maria@example.com'],
            $this->schema(),
        );

        $this->assertArrayHasKey('edad', $result);
        $this->assertNull($result['edad']);
    }
}
