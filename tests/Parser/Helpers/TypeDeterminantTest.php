<?php

declare(strict_types=1);

namespace Parser\Helpers;

use Enjoys\Dotenv\Parser\Helpers\TypeDeterminant;
use PHPUnit\Framework\TestCase;

class TypeDeterminantTest extends TestCase
{

    /**
     * @dataProvider dataForTestGetPossibleType
     */
    public function testAutoDetermineType($input, $expectType, $expectValue)
    {
        $expectValue ??= $input;
        $determinant = new TypeDeterminant($input);
        $this->assertSame($expectType, $determinant->getPossibleType());
        $this->assertSame($expectValue, $determinant->getCastValue());
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
            ['3,14', 'string', null],
            ['true', 'bool', true],
            ['false', 'bool', false],
            ['TruE', 'bool', true],
            ['faLSe', 'bool', false],
            ['', 'string', null],
            ['0xA', 'string', null],
            ['0755', 'string', null],
        ];
    }
}
