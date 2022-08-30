<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class DotenvTest extends TestCase
{

    /**
     * @param class-string|object $className
     * @param string $propertyName
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public function getPrivateProperty($className, string $propertyName): \ReflectionProperty
    {
        $reflector = new \ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    public function testBaseDirectory()
    {
        $this->assertSame(
            'var' . DIRECTORY_SEPARATOR,
            $this->getPrivateProperty(
                Dotenv::class,
                'baseDirectory'
            )->getValue(
                new Dotenv('var/')
            )
        );

        $this->assertSame(
            '/var' . DIRECTORY_SEPARATOR,
            $this->getPrivateProperty(
                Dotenv::class,
                'baseDirectory'
            )->getValue(
                new Dotenv('/var')
            )
        );
    }

    public function testVariableReplace()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/1');
        $dotenv->loadEnv();

        $this->assertSame('dev', $_ENV['APP_ENV']);
        $this->assertSame('C:/openserver/test', $_ENV['TEST_DIR']);
    }

    public function testVariableReplace2()
    {
        putenv('APP_ENV=test');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/1');
        $dotenv->loadEnv();

        $this->assertSame('test', $_ENV['APP_ENV']);
        $this->assertSame('/var/testing/test', $_ENV['TEST_DIR']);
        putenv('APP_ENV'); //unset
    }

    public function testVariableReplaceRecursive()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/4', 'env');
        $dotenv->loadEnv();

        $this->assertSame('/var/www/public', $_ENV['VAR2']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR3']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR4']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR5']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR6']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR7']);
    }

    public function testVariableReplaceRecursiveNonLineage()
    {
//        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/5', 'env');
        $dotenv->loadEnv();
        $this->assertSame('/var/www/public', $_ENV['VAR2']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR3']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR4']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR5']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR6']);
    }

    public function testVariablesNotFound()
    {
        $this->expectExceptionMessage('Not found variable ${BAZ}.');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.notfoundvars');
        $dotenv->loadEnv();
    }

    public function testCastType()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/2');
        $dotenv->loadEnv();

        $this->assertSame(42, $_ENV['VAR_1']);
        $this->assertSame('42', $_ENV['VAR_1_3']);
        $this->assertSame('test *int 5', $_ENV['VAR_1_4']);
        $this->assertSame(0755, $_ENV['VAR_1_5']);
        $this->assertSame(0xA, $_ENV['VAR_1_6']);
        $this->assertSame(true, $_ENV['VAR_2']);
        $this->assertSame(false, $_ENV['VAR_3']);
        $this->assertSame(null, $_ENV['VAR_4']);
        $this->assertSame(3.14, $_ENV['VAR_5']);
        $this->assertSame(3.14, $_ENV['VAR_6']);
        $this->assertSame('3,14', $_ENV['VAR_7']);
        $this->assertSame('3.14', $_ENV['VAR_7_1']);
        $this->assertSame('3.14', $_ENV['VAR_7_2']);
        $this->assertSame('', $_ENV['VAR_8']);
    }

    public function testEnvWithEq()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3');
        $dotenv->loadEnv();
        $this->assertSame('foo bar = zed', $_ENV['VAR']);
    }

    public function testUsePutEnvTrue()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3');
        $dotenv->loadEnv(true);
        $this->assertSame('foo bar = zed', getenv('VAR'));

        putenv('VAR'); //unset
    }

    public function testUsePutEnvFalse()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3');
        $dotenv->loadEnv();
        $this->assertSame(false, getenv('VAR'));
    }

    public function testNotOverriding()
    {
        $php_version = 'test';
        putenv("PHP_VERSION=$php_version");
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3');
        $dotenv->loadEnv();
        $this->assertSame($php_version, $_ENV['PHP_VERSION']);

        putenv('PHP_VERSION'); //unset
    }

    public function testInvalidInt()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.error.int');
        $dotenv->loadEnv();
    }

    public function testInvalidInt8()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.error.int8');
        $dotenv->loadEnv();
    }

    public function testInvalidInt16()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.error.int16');
        $dotenv->loadEnv();
    }

    public function testInvalidFloat()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.error.float');
        $dotenv->loadEnv();
    }

    public function testWithComment()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/with_comment');
        $dotenv->loadEnv();
        $this->assertSame(false, $_ENV['MY_VAR'] ?? false);
        $this->assertSame('      # 23', $_ENV['VAR2']);
        $this->assertSame('/var/www', $_ENV['VAR3']);
        $this->assertSame(null, $_ENV['VAR4']);
    }

    public function testInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.error.key');
        $dotenv->loadEnv();
    }

    public function testInvalidString()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.error.string');
        $dotenv->loadEnv();
    }

    public function testReplaceValueInEnvFilesDefinedInPutenvOrExport()
    {
        putenv('SYS_VAR=123');
        putenv('SYS_VAR2=456');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/without_defined_vars');
        $dotenv->loadEnv();
        $this->assertSame('123', $_ENV['VAR']);
        $this->assertSame('123', $_ENV['VAR2']);
        $this->assertSame('456', $_ENV['VAR3']);
        putenv('SYS_VAR');
        putenv('SYS_VAR2');
    }

    public function testReplaceValueInEnvFilesDefinedInPutenvOrExportButTheyWasBeDefinedInFiles()
    {
        putenv('SYS_VAR=SYS_VAR');
        putenv('SYS_VAR2=SYS_VAR2');
        putenv('DEFINED_VAR=987');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/without_defined_vars');
        $dotenv->loadEnv();
        $this->assertSame('987', $_ENV['VAR4']);
        putenv('SYS_VAR');
        putenv('SYS_VAR2');
        putenv('DEFINED_VAR');
    }

    public function testQuotes()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/with_qoutes_and_escaping', '.quotes');
        $dotenv->loadEnv();
        $this->assertSame('value in double quotes', $_ENV['VAR1']);
        $this->assertSame('value without quotes', $_ENV['VAR2']);
        $this->assertSame('value in single quotes', $_ENV['VAR3']);
        $this->assertSame('va"', $_ENV['VAR4']);
        $this->assertSame('va" lue"', $_ENV['VAR5']);
    }

    public function testSlashes()
    {
        putenv('VAR=test\"val');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/with_qoutes_and_escaping', '.slashes');
        $dotenv->loadEnv();
        $this->assertSame('double quote in middle " text', $_ENV['VAR1']);
        $this->assertSame("value. it's var #2", $_ENV['VAR2']);
        $this->assertSame("\\\\ - two backslashes. not's 4", $_ENV['VAR3']);
        $this->assertSame('test\"val', $_ENV['VAR4']);
        $this->assertSame($_ENV['VAR1'], $_ENV['VAR5']);
        $this->assertSame($_ENV['VAR4'], $_ENV['VAR']);
        putenv('VAR');
    }

    public function testEnvArrayAndEnvRawArray()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/1');
        $dotenv->loadEnv();
        $this->assertSame([
            'APP_ENV' => 'dev',
            'TEST_DIR' => '${APP_DIR}/test',
            'APP_DIR' => 'C:/openserver',
        ], $dotenv->getEnvRawArray());

        $this->assertSame([
            'APP_ENV' => 'dev',
            'TEST_DIR' => 'C:/openserver/test',
            'APP_DIR' => 'C:/openserver',
        ], $dotenv->getEnvArray());
    }
}
