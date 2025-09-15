<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class EnvFunctionTest extends TestCase
{
    private array $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Сохраняем оригинальные значения
        $this->originalEnv = $_ENV;

        // Очищаем переменные окружения для тестов
        $_ENV = [];
        putenv('TEST_VAR');
    }

    protected function tearDown(): void
    {
        // Восстанавливаем оригинальные значения
        $_ENV = $this->originalEnv;

        // Очищаем установленные переменные
        putenv('TEST_VAR');

        parent::tearDown();
    }

    public function testReturnsDefaultWhenVariableNotExists()
    {
        $result = env('NON_EXISTENT_VAR', 'default_value');
        $this->assertSame('default_value', $result);
    }

    public function testReturnsNullDefaultWhenVariableNotExists()
    {
        $result = env('NON_EXISTENT_VAR');
        $this->assertNull($result);
    }

    public function testGetsValueFromGetEnv()
    {
        putenv('TEST_VAR=test_value');

        $result = env('TEST_VAR');
        $this->assertSame('test_value', $result);
    }

    public function testGetsValueFromEnvArray()
    {
        $_ENV['TEST_VAR'] = 'env_array_value';

        $result = env('TEST_VAR');
        $this->assertSame('env_array_value', $result);
    }

    public function testGetEnvTakesPrecedenceOverEnvArray()
    {
        putenv('TEST_VAR=getenv_value');
        $_ENV['TEST_VAR'] = 'env_array_value';

        $result = env('TEST_VAR');
        $this->assertSame('getenv_value', $result);
    }

    public function testCustomCastFunction()
    {
        putenv('NUMBER_VAR=123');

        $castFunction = fn($value) => (int) $value;

        $result = env('NUMBER_VAR', null, $castFunction);
        $this->assertSame(123, $result);
        $this->assertIsInt($result);
    }

    public function testDefaultCastFunctionWithBooleanTrue()
    {
        putenv('BOOL_VAR=true');

        $result = env('BOOL_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCastFunctionWithBooleanFalse()
    {
        putenv('BOOL_VAR=false');

        $result = env('BOOL_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCastFunctionWithStarTrue()
    {
        putenv('STAR_TRUE_VAR=*true');

        $result = env('STAR_TRUE_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCastFunctionWithStarFalse()
    {
        putenv('STAR_FALSE_VAR=*false');

        $result = env('STAR_FALSE_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCastFunctionWithStarBoolTrue()
    {
        putenv('STAR_BOOL_TRUE_VAR=*bool something');

        $result = env('STAR_BOOL_TRUE_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCastFunctionWithStarBoolFalse()
    {
        putenv('STAR_BOOL_FALSE_VAR=*bool');

        $result = env('STAR_BOOL_FALSE_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCastFunctionWithStarInt()
    {
        putenv('STAR_INT_VAR=*int 42');

        $result = env('STAR_INT_VAR');
        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }

    public function testDefaultCastFunctionWithNumericString()
    {
        putenv('NUMERIC_STRING_VAR=42');

        $result = env('NUMERIC_STRING_VAR');
        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }


    public function testDefaultCastFunctionWithFloat()
    {
        putenv('FLOAT_VAR=3.14');

        $result = env('FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultCastFunctionWithCommaFloat()
    {
        putenv('COMMA_FLOAT_VAR=3,14');

        $result = env('COMMA_FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultCastFunctionWithStarFloat()
    {
        putenv('STAR_FLOAT_VAR=*float 3,14');

        $result = env('STAR_FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultCastFunctionWithStarDouble()
    {
        putenv('STAR_DOUBLE_VAR=*double 3.14');

        $result = env('STAR_DOUBLE_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultCastFunctionWithStarString()
    {
        putenv('STAR_STRING_VAR=*string *int');

        $result = env('STAR_STRING_VAR');
        $this->assertSame('*int', $result);
        $this->assertIsString($result);
    }

    public function testDefaultCastFunctionWithStarNull()
    {
        putenv('STAR_NULL_VAR=*null');

        $result = env('STAR_NULL_VAR');
        $this->assertNull($result);
    }

    public function testCustomCastWithDefaultValue()
    {
        $castFunction = fn($value) => strtoupper($value);

        $result = env('NON_EXISTENT_VAR', 'hello', $castFunction);
        $this->assertSame('HELLO', $result);
    }

    public function testEmptyStringValue()
    {
        putenv('EMPTY_VAR=');

        $result = env('EMPTY_VAR', true);
        $this->assertSame(true, $result);
    }
}
