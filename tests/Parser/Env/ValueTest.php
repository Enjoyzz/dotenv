<?php

declare(strict_types=1);

namespace Parser\Env;

use Enjoys\Dotenv\Parser\Env\Value;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{

    public function testValueCreateWithNeedQuotesTrue()
    {
        $value = new Value('42', '"');
        $this->assertSame('"42"', $value->__toString());


    }

    public function testValueCreateWithNeedQuotesFalse()
    {
        $value = new Value('42');
        $this->assertSame('42', $value->__toString());

        $value = new Value('true');
        $this->assertSame('true', $value->__toString());
    }

    public function testValueCreate()
    {
        $value = new Value('true');
        $this->assertNotTrue($value->getValue());
        $this->assertSame('true', $value->getValue());
    }
}
