<?php

declare(strict_types=1);

use App\Application\Shared\Schema\RuleParser;
use App\Application\Shared\Schema\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private function schema(string $rules): array
    {
        return RuleParser::parse(['rules' => $rules, 'label' => 'Test']);
    }

    // --- required ---

    public function test_required_rechaza_null(): void
    {
        $errors = Validator::validate(null, $this->schema('required|string'));
        $this->assertNotEmpty($errors);
    }

    public function test_required_rechaza_string_vacio(): void
    {
        $errors = Validator::validate('', $this->schema('required|string'));
        $this->assertNotEmpty($errors);
    }

    public function test_required_rechaza_string_solo_espacios(): void
    {
        $errors = Validator::validate('   ', $this->schema('required|string'));
        $this->assertNotEmpty($errors);
    }

    public function test_no_required_permite_null(): void
    {
        $errors = Validator::validate(null, $this->schema('string'));
        $this->assertEmpty($errors);
    }

    // --- email ---

    public function test_email_acepta_formato_valido(): void
    {
        $errors = Validator::validate(
            'test@example.com',
            $this->schema('required|email'),
        );
        $this->assertEmpty($errors);
    }

    public function test_email_rechaza_formato_invalido(): void
    {
        $errors = Validator::validate(
            'no-es-un-email',
            $this->schema('required|email'),
        );
        $this->assertNotEmpty($errors);
    }

    // --- max (longitud string) ---

    public function test_max_acepta_longitud_igual_al_limite(): void
    {
        $errors = Validator::validate('12345', $this->schema('string|max:5'));
        $this->assertEmpty($errors);
    }

    public function test_max_rechaza_longitud_mayor_al_limite(): void
    {
        $errors = Validator::validate('123456', $this->schema('string|max:5'));
        $this->assertNotEmpty($errors);
    }

    // --- min_value / max_value ---

    public function test_min_value_rechaza_por_debajo(): void
    {
        $errors = Validator::validate(0, $this->schema('int|min_value:1'));
        $this->assertNotEmpty($errors);
    }

    public function test_min_value_acepta_igual(): void
    {
        $errors = Validator::validate(1, $this->schema('int|min_value:1'));
        $this->assertEmpty($errors);
    }

    public function test_max_value_rechaza_por_encima(): void
    {
        $errors = Validator::validate(10, $this->schema('int|max_value:9'));
        $this->assertNotEmpty($errors);
    }

    public function test_max_value_acepta_igual(): void
    {
        $errors = Validator::validate(9, $this->schema('int|max_value:9'));
        $this->assertEmpty($errors);
    }

    // --- regex ---

    public function test_regex_acepta_coincidencia(): void
    {
        $errors = Validator::validate(
            'Seat Ibiza 2020',
            $this->schema('string|regex:/^[a-zA-Z0-9\s]+$/'),
        );
        $this->assertEmpty($errors);
    }

    public function test_regex_rechaza_no_coincidencia(): void
    {
        $errors = Validator::validate(
            'Seat Ibiza @#$',
            $this->schema('string|regex:/^[a-zA-Z0-9\s]+$/'),
        );
        $this->assertNotEmpty($errors);
    }

    // --- date ---

    public function test_date_acepta_fecha_valida(): void
    {
        $errors = Validator::validate('2026-08-07', $this->schema('date'));
        $this->assertEmpty($errors);
    }

    public function test_date_rechaza_fecha_invalida(): void
    {
        $errors = Validator::validate('no-es-una-fecha', $this->schema('date'));
        $this->assertNotEmpty($errors);
    }

    // --- tipos ---

    public function test_type_string_rechaza_no_string(): void
    {
        $errors = Validator::validate(123, $this->schema('string'));
        $this->assertNotEmpty($errors);
    }

    public function test_type_int_rechaza_no_int(): void
    {
        $errors = Validator::validate('no-es-int', $this->schema('int'));
        $this->assertNotEmpty($errors);
    }

    public function test_type_uuid_acepta_formato_valido(): void
    {
        $errors = Validator::validate(
            '018f2e2e-1234-7000-8000-abcdefabcdef',
            $this->schema('uuid'),
        );
        $this->assertEmpty($errors);
    }

    public function test_type_uuid_rechaza_formato_invalido(): void
    {
        $errors = Validator::validate('no-es-uuid', $this->schema('uuid'));
        $this->assertNotEmpty($errors);
    }
}
