<?php

declare(strict_types=1);


use Enjoys\Dotenv\Helper;
use Enjoys\Dotenv\Variables;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{

    #[DataProvider('dataForTestScalarToString')]
    public function testScalarToString($input, $expect)
    {
        $value = Variables::scalarValueToString($input);
        $this->assertSame($expect, $value);
    }

    public static function dataForTestScalarToString(): array
    {
        return [
            [true, 'true'],
            [false, 'false'],
            [42, '42'],
            [3.14, '3.14'],
            [null, ''],
            ['string', 'string'],
        ];
    }
}
