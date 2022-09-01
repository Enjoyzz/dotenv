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
    public function testGetPossibleType($input, $expect)
    {
        $determinant = new TypeDeterminant($input);
        $this->assertSame($expect, $determinant->getPossibleType());
    }

    public function dataForTestGetPossibleType(): array
    {
        return [
            ['42', 'int'],
            ['i39', 'string'],
            ['12i', 'string'],
            ['0', 'int'],
            ['000', 'string'],
            ['3.14', 'float'],
            ['3,14', 'string'],
            ['true', 'bool'],
            ['false', 'bool'],
            ['TruE', 'bool'],
            ['faLSe', 'bool'],
            ['', 'string'],
            ['0xA', 'string'],
            ['0755', 'string'],
        ];
    }
}
