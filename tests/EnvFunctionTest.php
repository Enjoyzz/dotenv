<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class EnvFunctionTest extends TestCase
{
    private $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalEnv = $_ENV;
        $_ENV = [];
        putenv('TEST_VAR');
    }

    protected function tearDown(): void
    {
        $_ENV = $this->originalEnv;
        putenv('TEST_VAR');
        parent::tearDown();
    }

    public function testReturnsDefaultWhenVariableNotExists()
    {
        $result = env('NON_EXISTENT_VAR', 'default_value');
        $this->assertEquals('default_value', $result);
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
        $this->assertEquals('test_value', $result);
    }

    public function testGetsValueFromEnvArray()
    {
        $_ENV['TEST_VAR'] = 'env_array_value';

        $result = env('TEST_VAR');
        $this->assertEquals('env_array_value', $result);
    }

    public function testGetEnvTakesPrecedenceOverEnvArray()
    {
        putenv('TEST_VAR=getenv_value');
        $_ENV['TEST_VAR'] = 'env_array_value';

        $result = env('TEST_VAR');
        $this->assertEquals('getenv_value', $result);
    }

    public function testDefaultTransformFunctionWithBooleanTrue()
    {
        putenv('BOOL_VAR=true');

        $result = env('BOOL_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultTransformFunctionWithBooleanFalse()
    {
        putenv('BOOL_VAR=false');

        $result = env('BOOL_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultTransformFunctionWithStarTrue()
    {
        putenv('STAR_TRUE_VAR=*true');

        $result = env('STAR_TRUE_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultTransformFunctionWithStarFalse()
    {
        putenv('STAR_FALSE_VAR=*false');

        $result = env('STAR_FALSE_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultTransformFunctionWithStarBoolTrue()
    {
        putenv('STAR_BOOL_TRUE_VAR=*bool something');

        $result = env('STAR_BOOL_TRUE_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultTransformFunctionWithStarBoolFalse()
    {
        putenv('STAR_BOOL_FALSE_VAR=*bool');

        $result = env('STAR_BOOL_FALSE_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultTransformFunctionWithStarInt()
    {
        putenv('STAR_INT_VAR=*int 42');

        $result = env('STAR_INT_VAR');
        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }

    public function testDefaultTransformFunctionWithNumericString()
    {
        putenv('NUMERIC_STRING_VAR=42');

        $result = env('NUMERIC_STRING_VAR');
        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }

    public function testDefaultTransformFunctionWithFloat()
    {
        putenv('FLOAT_VAR=3.14');

        $result = env('FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultTransformFunctionWithCommaFloat()
    {
        putenv('COMMA_FLOAT_VAR=3,14');

        $result = env('COMMA_FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultTransformFunctionWithStarFloat()
    {
        putenv('STAR_FLOAT_VAR=*float 3,14');

        $result = env('STAR_FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultTransformFunctionWithStarDouble()
    {
        putenv('STAR_DOUBLE_VAR=*double 3.14');

        $result = env('STAR_DOUBLE_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultTransformFunctionWithStarString()
    {
        putenv('STAR_STRING_VAR=*string *int');

        $result = env('STAR_STRING_VAR');
        $this->assertEquals('*int', $result);
        $this->assertIsString($result);
    }

    public function testDefaultTransformFunctionWithStarNull()
    {
        putenv('STAR_NULL_VAR=*null');

        $result = env('STAR_NULL_VAR');
        $this->assertNull($result);
    }

    public function testCustomTransformFunction()
    {
        putenv('NUMBER_VAR=123');

        $result = env('NUMBER_VAR', null, fn($v) => (int) $v);
        $this->assertSame(123, $result);
        $this->assertIsInt($result);
    }

    public function testCustomTransformWithDefaultValue()
    {
        $result = env('NON_EXISTENT_VAR', 'hello', fn($v) => strtoupper($v));
        $this->assertEquals('HELLO', $result);
    }

    public function testEmptyStringValue()
    {
        putenv('EMPTY_VAR=');

        $result = env('EMPTY_VAR', 'default');
        $this->assertEquals('', $result);
    }

    public function testRawModeReturnsOriginalValue()
    {
        putenv('TEST_VAR=123');

        $result = env('TEST_VAR', null, null, null, true);
        $this->assertEquals('123', $result);
        $this->assertIsString($result);
    }

    public function testRawModeWithDefaultValue()
    {
        $result = env('NON_EXISTENT_VAR', 'default', null, null, true);
        $this->assertEquals('default', $result);
    }

    public function testRawModeIgnoresTransformAndValidator()
    {
        putenv('TEST_VAR=123');

        $transform = fn($v) => (int) $v;
        $validator = fn($v) => $v > 100;

        $result = env('TEST_VAR', null, $transform, $validator, true);
        $this->assertEquals('123', $result);
        $this->assertIsString($result);
    }

    public function testValidatorPasses()
    {
        putenv('PORT_VAR=8080');

        $validator = fn($v) => $v >= 1 && $v <= 65535;

        $result = env('PORT_VAR', 3000, fn($v) => (int) $v, $validator);
        $this->assertSame(8080, $result);
    }

    public function testValidatorFailsThrowsException()
    {
        putenv('PORT_VAR=70000');

        $validator = fn($v) => $v >= 1 && $v <= 65535;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Environment variable "PORT_VAR" validation failed');

        env('PORT_VAR', 3000, fn($v) => (int) $v, $validator);
    }

    public function testValidatorWithCustomErrorMessage()
    {
        putenv('PORT_VAR=70000');

        $validator = function($v) {
            if ($v < 1 || $v > 65535) {
                throw new InvalidArgumentException("Port $v is out of range");
            }
            return true;
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Port 70000 is out of range');

        env('PORT_VAR', 3000, fn($v) => (int) $v, $validator);
    }

    public function testValidatorReceivesTransformedValue()
    {
        putenv('NUMBER_VAR=42');

        $transform = fn($v) => (int) $v;
        $validator = fn($v) => is_int($v);

        $result = env('NUMBER_VAR', 0, $transform, $validator);
        $this->assertSame(42, $result);
    }

    public function testValidatorWithBooleanLogic()
    {
        putenv('FEATURE_FLAG=true');

        $validator = fn($v) => is_bool($v);

        $result = env('FEATURE_FLAG', false, null, $validator);
        $this->assertTrue($result);
    }

    public function testComplexTransformAndValidator()
    {
        putenv('HOSTS=localhost,127.0.0.1,example.com');

        $transform = fn($v) => array_filter(array_map('trim', explode(',', $v)));
        $validator = fn($v) => is_array($v) && count($v) > 0;

        $result = env('HOSTS', [], $transform, $validator);
        $this->assertSame(['localhost', '127.0.0.1', 'example.com'], $result);
    }

    public function testValidatorWithDefaultValue()
    {
        $validator = fn($v) => $v >= 1 && $v <= 65535;

        $result = env('NON_EXISTENT_PORT', 8080, fn($v) => (int) $v, $validator);
        $this->assertSame(8080, $result);
    }

    public function testValidatorFailsOnDefaultValue()
    {
        $validator = fn($v) => $v >= 1 && $v <= 65535;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Environment variable "INVALID_PORT" validation failed');

        env('INVALID_PORT', 70000, fn($v) => (int) $v, $validator);
    }

    public function testOnlyValidatorWithoutTransform()
    {
        putenv('BOOLEAN_VAR=true');

        $validator = fn($v) => is_bool($v);

        $result = env('BOOLEAN_VAR', false, null, $validator);
        $this->assertTrue($result);
    }

    public function testOnlyTransformWithoutValidator()
    {
        putenv('NUMBER_VAR=42');

        $result = env('NUMBER_VAR', 0, fn($v) => (int) $v);
        $this->assertSame(42, $result);
    }

    public function testNullValueWithValidator()
    {
        putenv('NULL_VAR=Null');

        $validator = fn($v) => $v === null;

        $result = env('NULL_VAR', 'not_null', null, $validator);
        $this->assertNull($result);
    }

    public function testEmptyArrayValidator()
    {
        putenv('EMPTY_ARRAY_VAR=');

        $transform = fn($v) => explode(',', $v);
        $validator = fn($v) => is_array($v) && count($v) === 1 && empty($v[0]);

        $result = env('EMPTY_ARRAY_VAR', [], $transform, $validator);
        $this->assertSame([''], $result);
    }
}
