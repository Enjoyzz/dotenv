<?php

declare(strict_types=1);

namespace Parser\Env;

use Enjoys\Dotenv\Exception\InvalidArgumentException;
use Enjoys\Dotenv\Parser\Env\Key;
use PHPUnit\Framework\TestCase;

class KeyTest extends TestCase
{
    /**
     * @dataProvider dataForTestValidKeys
     */
    public function testValidKeys($value, $expect): void
    {
        if ($expect === false){
            $this->expectException(InvalidArgumentException::class);
        }
        $key = new Key($value);
        $this->assertSame($value, $key->getValue());
    }

    public function dataForTestValidKeys(): array
    {
        return [
            ['VAR1', true],
            ['A', true],
            ['VAR_1', true],
            ['_VAR_', true],
            ['42', false],
            ['1VAR', false],
            ['VAR-1', false],
            ['', false],
            ['var', false],
        ];
    }
}
