<?php

declare(strict_types=1);


use Enjoys\Dotenv\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * @dataProvider dataForTestScalarToString
     */
    public function testScalarToString($input, $expect)
    {
        $value = Helper::scalarValueToString($input);
        $this->assertSame($expect, $value);
    }

    public function dataForTestScalarToString()
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
