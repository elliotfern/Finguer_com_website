<?php

declare(strict_types=1);

use App\Application\Shared\Schema\RuleParser;
use PHPUnit\Framework\TestCase;

class RuleParserTest extends TestCase
{
    public function test_parsea_required(): void
    {
        $parsed = RuleParser::parse(['rules' => 'required', 'label' => 'Test']);

        $this->assertSame([['name' => 'required']], $parsed['rules']);
    }

    public function test_parsea_max_con_valor(): void
    {
        $parsed = RuleParser::parse(['rules' => 'max:255', 'label' => 'Test']);

        $this->assertSame(
            [['name' => 'max', 'value' => 255]],
            $parsed['rules'],
        );
    }

    public function test_parsea_min_value_con_valor_float(): void
    {
        $parsed = RuleParser::parse([
            'rules' => 'min_value:1',
            'label' => 'Test',
        ]);

        $this->assertSame(
            [['name' => 'min_value', 'value' => 1.0]],
            $parsed['rules'],
        );
    }

    public function test_parsea_max_value_con_valor_float(): void
    {
        $parsed = RuleParser::parse([
            'rules' => 'max_value:9',
            'label' => 'Test',
        ]);

        $this->assertSame(
            [['name' => 'max_value', 'value' => 9.0]],
            $parsed['rules'],
        );
    }

    public function test_parsea_regex_con_patron(): void
    {
        $parsed = RuleParser::parse([
            'rules' => 'regex:/^[a-z]+$/',
            'label' => 'Test',
        ]);

        $this->assertSame(
            [['name' => 'regex', 'value' => '/^[a-z]+$/']],
            $parsed['rules'],
        );
    }

    public function test_parsea_multiples_reglas_combinadas(): void
    {
        $parsed = RuleParser::parse([
            'rules' => 'required|int|min_value:1|max_value:9',
            'label' => 'Test',
        ]);

        $this->assertCount(3, $parsed['rules']);
        $this->assertSame('int', $parsed['type']);
    }

    public function test_parsea_type_string(): void
    {
        $parsed = RuleParser::parse([
            'rules' => 'string|max:10',
            'label' => 'Test',
        ]);

        $this->assertSame('string', $parsed['type']);
    }

    public function test_parsea_type_int(): void
    {
        $parsed = RuleParser::parse([
            'rules' => 'int|min_value:0',
            'label' => 'Test',
        ]);

        $this->assertSame('int', $parsed['type']);
    }

    public function test_conserva_label(): void
    {
        $parsed = RuleParser::parse([
            'rules' => 'required',
            'label' => 'Mi Campo',
        ]);

        $this->assertSame('Mi Campo', $parsed['label']);
    }
}
