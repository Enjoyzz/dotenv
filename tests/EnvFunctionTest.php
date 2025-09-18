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

    public function testDefaultCallbackFunctionWithBooleanTrue()
    {
        putenv('BOOL_VAR=true');

        $result = env('BOOL_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCallbackFunctionWithBooleanFalse()
    {
        putenv('BOOL_VAR=false');

        $result = env('BOOL_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCallbackFunctionWithStarTrue()
    {
        putenv('STAR_TRUE_VAR=*true');

        $result = env('STAR_TRUE_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCallbackFunctionWithStarFalse()
    {
        putenv('STAR_FALSE_VAR=*false');

        $result = env('STAR_FALSE_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCallbackFunctionWithStarBoolTrue()
    {
        putenv('STAR_BOOL_TRUE_VAR=*bool something');

        $result = env('STAR_BOOL_TRUE_VAR');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCallbackFunctionWithStarBoolFalse()
    {
        putenv('STAR_BOOL_FALSE_VAR=*bool');

        $result = env('STAR_BOOL_FALSE_VAR');
        $this->assertFalse($result);
        $this->assertIsBool($result);
    }

    public function testDefaultCallbackFunctionWithStarInt()
    {
        putenv('STAR_INT_VAR=*int 42');

        $result = env('STAR_INT_VAR');
        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }

    public function testDefaultCallbackFunctionWithNumericString()
    {
        putenv('NUMERIC_STRING_VAR=42');

        $result = env('NUMERIC_STRING_VAR');
        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }

    public function testDefaultCallbackFunctionWithFloat()
    {
        putenv('FLOAT_VAR=3.14');

        $result = env('FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultCallbackFunctionWithCommaFloat()
    {
        putenv('COMMA_FLOAT_VAR=3,14');

        $result = env('COMMA_FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultCallbackFunctionWithStarFloat()
    {
        putenv('STAR_FLOAT_VAR=*float 3,14');

        $result = env('STAR_FLOAT_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultCallbackFunctionWithStarDouble()
    {
        putenv('STAR_DOUBLE_VAR=*double 3.14');

        $result = env('STAR_DOUBLE_VAR');
        $this->assertSame(3.14, $result);
        $this->assertIsFloat($result);
    }

    public function testDefaultCallbackFunctionWithStarString()
    {
        putenv('STAR_STRING_VAR=*string *int');

        $result = env('STAR_STRING_VAR');
        $this->assertEquals('*int', $result);
        $this->assertIsString($result);
    }

    public function testDefaultCallbackFunctionWithStarNull()
    {
        putenv('STAR_NULL_VAR=*null');

        $result = env('STAR_NULL_VAR');
        $this->assertNull($result);
    }

    public function testCustomCallbackFunction()
    {
        putenv('NUMBER_VAR=123');

        $result = env('NUMBER_VAR', null, fn($v) => (int) $v);
        $this->assertSame(123, $result);
        $this->assertIsInt($result);
    }

    public function testCustomCallbackWithDefaultValue()
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

    public function testRawModeWithCustomCallback()
    {
        putenv('TEST_VAR=123');

        $result = env('TEST_VAR', null, function($value) {
            return $value; // возвращаем как есть (аналог raw mode)
        });
        $this->assertEquals('123', $result);
        $this->assertIsString($result);
    }

    public function testCallbackWithKeyParameter()
    {
        putenv('TEST_VAR=value');

        $result = env('TEST_VAR', null, function($value, $key) {
            return "{$key}:{$value}";
        });
        $this->assertEquals('TEST_VAR:value', $result);
    }

    public function testCallbackWithValidationAndTransformation()
    {
        putenv('PORT_VAR=8080');

        $result = env('PORT_VAR', 3000, function($value, $key) {
            $value = (int) $value;
            if ($value < 1 || $value > 65535) {
                throw new InvalidArgumentException("Port $value is out of range for $key");
            }
            return $value;
        });
        $this->assertSame(8080, $result);
    }

    public function testCallbackValidationFailsThrowsException()
    {
        putenv('PORT_VAR=70000');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Port 70000 is out of range for PORT_VAR');

        env('PORT_VAR', 3000, function($value, $key) {
            $value = (int) $value;
            if ($value < 1 || $value > 65535) {
                throw new InvalidArgumentException("Port $value is out of range for $key");
            }
            return $value;
        });
    }

    public function testCallbackReceivesTransformedValue()
    {
        putenv('NUMBER_VAR=42');

        $result = env('NUMBER_VAR', 0, function($value) {
            $value = (int) $value;
            if (!is_int($value)) {
                throw new InvalidArgumentException("Expected integer");
            }
            return $value;
        });
        $this->assertSame(42, $result);
    }

    public function testCallbackWithBooleanLogic()
    {
        putenv('FEATURE_FLAG=true');

        $result = env('FEATURE_FLAG', false, function($value) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            if (!is_bool($value)) {
                throw new InvalidArgumentException("Expected boolean");
            }
            return $value;
        });
        $this->assertTrue($result);
    }

    public function testComplexCallback()
    {
        putenv('HOSTS=localhost,127.0.0.1,example.com');

        $result = env('HOSTS', [], function($value) {
            $hosts = array_filter(array_map('trim', explode(',', $value)));
            if (count($hosts) === 0) {
                throw new InvalidArgumentException("At least one host required");
            }
            return $hosts;
        });
        $this->assertSame(['localhost', '127.0.0.1', 'example.com'], $result);
    }

    public function testCallbackWithDefaultValue()
    {
        $result = env('NON_EXISTENT_PORT', 8080, function($value) {
            $value = (int) $value;
            if ($value < 1 || $value > 65535) {
                throw new InvalidArgumentException("Port out of range");
            }
            return $value;
        });
        $this->assertSame(8080, $result);
    }

    public function testCallbackFailsOnDefaultValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Port out of range');

        env('INVALID_PORT', 70000, function($value) {
            $value = (int) $value;
            if ($value < 1 || $value > 65535) {
                throw new InvalidArgumentException("Port out of range");
            }
            return $value;
        });
    }

    public function testOnlyTransformationWithoutValidation()
    {
        putenv('NUMBER_VAR=42');

        $result = env('NUMBER_VAR', 0, fn($v) => (int) $v);
        $this->assertSame(42, $result);
    }

    public function testNullValueWithCallback()
    {
        putenv('NULL_VAR=Null');

        $result = env('NULL_VAR', 'not_null', function($value) {
            if (strtolower($value) === 'null') {
                return null;
            }
            return $value;
        });
        $this->assertNull($result);
    }

    public function testEmptyArrayCallback()
    {
        putenv('EMPTY_ARRAY_VAR=');

        $result = env('EMPTY_ARRAY_VAR', [], function($value) {
            $array = explode(',', $value);
            return count($array) === 1 && empty($array[0]) ? [''] : $array;
        });
        $this->assertSame([''], $result);
    }

    public function testCallbackWithCustomErrorMessage()
    {
        putenv('PORT_VAR=70000');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom error message for PORT_VAR');

        env('PORT_VAR', 3000, function($value, $key) {
            $value = (int) $value;
            if ($value < 1 || $value > 65535) {
                throw new InvalidArgumentException("Custom error message for $key");
            }
            return $value;
        });
    }
}
