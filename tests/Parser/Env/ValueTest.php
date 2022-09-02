<?php

declare(strict_types=1);

namespace Parser\Env;

use Enjoys\Dotenv\Parser\Env\Value;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    public function testValueCreateWithAutoCastToInt()
    {
        $value = new Value('42', autoCastType: true);
        $this->assertIsInt($value->getValue());
    }

    public function testValueCreateWithAutoCastToFloat()
    {
        $value = new Value('3.14', autoCastType: true);
        $this->assertIsFloat($value->getValue());
    }

    public function testValueCreateWithAutoCastToBool()
    {
        $value = new Value('true', autoCastType: true);
        $this->assertTrue($value->getValue());
        $value = new Value('False', autoCastType: true);
        $this->assertFalse($value->getValue());
    }

    public function testValueCreateWithNeedQuotesTrue()
    {
        $value = new Value('42', true);
        $this->assertSame('"42"', $value->__toString());


    }

    public function testValueCreateWithNeedQuotesFalse()
    {
        $value = new Value('42', false);
        $this->assertSame('42', $value->__toString());

        $value = new Value('true', autoCastType: true);
        $this->assertSame('true', $value->__toString());
    }

    public function testValueCreateWithAutoCastDisabled()
    {
        $value = new Value('true');
        $this->assertNotTrue($value->getValue());
        $this->assertSame('true', $value->getValue());
    }
}
