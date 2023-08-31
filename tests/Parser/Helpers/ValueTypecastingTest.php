<?php

declare(strict_types=1);

namespace Parser\Helpers;

use Enjoys\Dotenv\ValueTypeCasting;
use PHPUnit\Framework\TestCase;

class ValueTypecastingTest extends TestCase
{

    /**
     * @dataProvider dataForTestGetPossibleType
     */
    public function testAutoDetermineType($input, $expectType, $expectValue)
    {
        $expectValue ??= $input;
        $determinant = new ValueTypeCasting($input);
        $value = $determinant->getCastValue();
        $this->assertSame($expectType, get_debug_type($value));
        $this->assertSame($expectValue, $value);
    }

    public function dataForTestGetPossibleType(): array
    {
        return [
            ['42', 'int', 42],
            ['i39', 'string', null],
            ['12i', 'string', null],
            ['0', 'int', 0],
            ['000', 'string', null],
            ['3.14', 'float', 3.14],
            ['3,14', 'float', 3.14],
            ['*double 3,14', 'float', 3.14],
            ['true', 'bool', true],
            ['false', 'bool', false],
            ['TruE', 'bool', true],
            ['faLSe', 'bool', false],
            ['', 'string', null],
            ['0xA', 'string', null],
            ['0755', 'string', null],
            ['*bool', 'bool', false],
            ['*bool true', 'bool', true],
            ['*int', 'string', null],
            ['*int 42', 'int', 42],
            [' *int 42', 'string', null],
            [' *float 42', 'string', null],
            ['*string *int', 'string', '*int'],
        ];
    }
}
