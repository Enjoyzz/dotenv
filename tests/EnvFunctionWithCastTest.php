<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class EnvFunctionWithCastTest extends TestCase
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

    public function testWithIntvalFunction()
    {
        putenv('PORT=8080');

        $result = env('PORT', 3000, 'intval');
        $this->assertSame(8080, $result);
        $this->assertIsInt($result);
    }

    public function testWithIntvalFunctionOnString()
    {
        putenv('PORT=8080');

        $result = env('PORT', '3000', 'intval');
        $this->assertSame(8080, $result);
        $this->assertIsInt($result);
    }

    public function testWithIntvalFunctionWithDefault()
    {
        $result = env('NON_EXISTENT_PORT', 3000, 'intval');
        $this->assertSame(3000, $result);
        $this->assertIsInt($result);
    }

    public function testWithFloatvalFunction()
    {
        putenv('PRICE=99.99');

        $result = env('PRICE', 0.0, 'floatval');
        $this->assertSame(99.99, $result);
        $this->assertIsFloat($result);
    }

    public function testWithBoolvalFunction()
    {
        putenv('DEBUG=true');

        $result = env('DEBUG', false, 'boolval');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testWithBoolvalFunctionOnString()
    {
        putenv('ENABLED=1');

        $result = env('ENABLED', false, 'boolval');
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testWithStrvalFunction()
    {
        putenv('NUMBER=42');

        $result = env('NUMBER', '0', 'strval');
        $this->assertSame('42', $result);
        $this->assertIsString($result);
    }

    public function testWithTrimFunction()
    {
        putenv('NAME=  John Doe  ');

        $result = env('NAME', '', 'trim');
        $this->assertSame('John Doe', $result);
    }

    public function testWithStrtoupperFunction()
    {
        putenv('ENV=production');

        $result = env('ENV', 'dev', 'strtoupper');
        $this->assertSame('PRODUCTION', $result);
    }

    public function testWithStrtolowerFunction()
    {
        putenv('KEY=API_KEY_VALUE');

        $result = env('KEY', '', 'strtolower');
        $this->assertSame('api_key_value', $result);
    }

    public function testWithArrowFunctionIntCast()
    {
        putenv('TIMEOUT=30');

        $result = env('TIMEOUT', 10, fn($v) => (int)$v);
        $this->assertSame(30, $result);
        $this->assertIsInt($result);
    }

    public function testWithArrowFunctionFloatCast()
    {
        putenv('RATE=1.5');

        $result = env('RATE', 1.0, fn($v) => (float)$v);
        $this->assertSame(1.5, $result);
        $this->assertIsFloat($result);
    }

    public function testWithArrowFunctionBoolCast()
    {
        putenv('ENABLED=true');

        $result = env('ENABLED', false, fn($v) => (bool)$v);
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testWithArrowFunctionStringCast()
    {
        putenv('ID=123');

        $result = env('ID', '0', fn($v) => (string)$v);
        $this->assertSame('123', $result);
        $this->assertIsString($result);
    }

    public function testWithArrowFunctionValidation()
    {
        putenv('PORT=8080');

        $result = env('PORT', 3000, function ($v) {
            $port = (int)$v;
            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException('Invalid port number');
            }
            return $port;
        });

        $this->assertSame(8080, $result);
    }

    public function testWithArrowFunctionValidationThrowsException()
    {
        putenv('PORT=70000');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid port number');

        env('PORT', 3000, function ($v) {
            $port = (int)$v;
            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException('Invalid port number');
            }
            return $port;
        });
    }

    public function testWithArrowFunctionArrayConversion()
    {
        putenv('HOSTS=localhost,127.0.0.1,example.com');

        $result = env('HOSTS', '', fn($v) => explode(',', $v));
        $this->assertSame(['localhost', '127.0.0.1', 'example.com'], $result);
        $this->assertIsArray($result);
    }

    public function testWithArrowFunctionArrayConversionWithTrim()
    {
        putenv('HOSTS= localhost , 127.0.0.1 , example.com ');

        $result = env('HOSTS', '', function ($v) {
            return array_map('trim', explode(',', $v));
        });

        $this->assertSame(['localhost', '127.0.0.1', 'example.com'], $result);
    }

    public function testWithArrowFunctionJsonDecode()
    {
        putenv('CONFIG={"debug":true,"timeout":30}');

        $result = env('CONFIG', '{}', fn($v) => json_decode($v, true));
        $this->assertSame(['debug' => true, 'timeout' => 30], $result);
        $this->assertIsArray($result);
    }

    public function testWithArrowFunctionJsonDecodeInvalidJson()
    {
        putenv('CONFIG=invalid_json');

        $result = env('CONFIG', '{}', function ($v) {
            $decoded = json_decode($v, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        });

        $this->assertSame([], $result);
    }

    public function testWithArrowFunctionRangeLimitation()
    {
        putenv('RETRIES=15');

        $result = env('RETRIES', 3, fn($v) => max(1, min(10, (int)$v)));
        $this->assertSame(10, $result); // Ограничено максимум 10
    }

    public function testWithArrowFunctionComplexTransformation()
    {
        putenv('EMAIL=  USER@EXAMPLE.COM  ');

        $result = env('EMAIL', '', function ($v) {
            return strtolower(trim($v));
        });

        $this->assertSame('user@example.com', $result);
    }

    public function testWithArrowFunctionDefaultValueTransformation()
    {
        $result = env('NON_EXISTENT', '  DEFAULT  ', 'trim');
        $this->assertSame('DEFAULT', $result);
    }

    public function testWithArrowFunctionNullHandling()
    {
        putenv('NULL_VALUE=null');

        $result = env('NULL_VALUE', 'default', function ($v) {
            return $v === 'null' ? null : $v;
        });

        $this->assertNull($result);
    }

    public function testWithMultipleTransformationsUsingCompose()
    {
        $compose = function (callable ...$functions) {
            return fn($value)
                => array_reduce(
                $functions,
                fn($carry, $function) => $function($carry),
                $value,
            );
        };

        putenv('USERNAME=  ADMIN  ');

        $transform = $compose('trim', 'strtolower');
        $result = env('USERNAME', '', $transform);

        $this->assertSame('admin', $result);
    }

    public function testWithClosureThatReturnsDifferentType()
    {
        putenv('COUNT=5');

        $result = env('COUNT', 1, function ($v) {
            return "Value: " . (int)$v;
        });

        $this->assertSame('Value: 5', $result);
        $this->assertIsString($result);
    }

    public function testCastFunctionReceivesCorrectValueTypes()
    {
        putenv('STRING_VALUE=test');
        putenv('NUMERIC_VALUE=42');
        putenv('BOOLEAN_VALUE=true');

        $stringResult = env('STRING_VALUE', '', fn($v) => gettype($v));
        $numericResult = env('NUMERIC_VALUE', 0, fn($v) => gettype($v));
        $booleanResult = env('BOOLEAN_VALUE', false, fn($v) => gettype($v));

        $this->assertSame('string', $stringResult);
        $this->assertSame('string', $numericResult); // Значение из env всегда string
        $this->assertSame('string', $booleanResult); // Значение из env всегда string
    }

    public function testCastFunctionWithEmptyString()
    {
        putenv('EMPTY_VALUE=');

        $result = env('EMPTY_VALUE', 'default', fn($v) => empty($v) ? 'empty' : $v);
        $this->assertSame('empty', $result);
    }

    public function testCastFunctionWithZero()
    {
        putenv('ZERO_VALUE=0');

        $result = env('ZERO_VALUE', -1, fn($v) => (int)$v);
        $this->assertSame(0, $result);
    }

    public function testReusableValidatorFunctions()
    {
        $validators = [
            'port' => fn($v) => max(1, min(65535, (int)$v)),
            'bool' => fn($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN),
            'array' => fn($v) => array_filter(array_map('trim', explode(',', $v))),
        ];

        putenv('APP_PORT=8080');
        putenv('APP_DEBUG=true');
        putenv('APP_HOSTS=localhost, 127.0.0.1, ');

        $port = env('APP_PORT', 3000, $validators['port']);
        $debug = env('APP_DEBUG', false, $validators['bool']);
        $hosts = env('APP_HOSTS', '', $validators['array']);

        $this->assertSame(8080, $port);
        $this->assertTrue($debug);
        $this->assertSame(['localhost', '127.0.0.1'], $hosts);
    }
}
