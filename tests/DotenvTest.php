<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class DotenvTest extends TestCase
{

    protected function setUp(): void
    {
        Dotenv::clear();
    }

    /**
     * @param class-string|object $className
     * @param string $propertyName
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public function getPrivateProperty($className, string $propertyName): ReflectionProperty
    {
        $reflector = new ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

//    public function testBaseDirectory()
//    {
//        $this->assertSame(
//            'var' . DIRECTORY_SEPARATOR,
//            $this->getPrivateProperty(
//                Dotenv::class,
//                'baseDirectory'
//            )->getValue(
//                new Dotenv('var/')
//            )
//        );
//
//        $this->assertSame(
//            '/var' . DIRECTORY_SEPARATOR,
//            $this->getPrivateProperty(
//                Dotenv::class,
//                'baseDirectory'
//            )->getValue(
//                new Dotenv('/var')
//            )
//        );
//    }

    public function testVariableReplace()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/1/.env');
        $dotenv->loadEnv();

        $this->assertSame('dev', $_ENV['APP_ENV']);
        $this->assertSame('C:/openserver/test', $_ENV['TEST_DIR']);
    }

    public function testVariableReplace2()
    {
        putenv('APP_ENV=test');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/1/.env');
        $dotenv->loadEnv();

        $this->assertSame('test', $_ENV['APP_ENV']);
        $this->assertSame('/var/testing/test', $_ENV['TEST_DIR']);
        putenv('APP_ENV'); //unset
    }

    public function testVariableReplaceRecursive()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/4/env');
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
        $dotenv = new Dotenv(__DIR__ . '/fixtures/5/env');
        $dotenv->loadEnv();
        $this->assertSame('/var/www/public', $_ENV['VAR2']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR3']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR4']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR5']);
        $this->assertSame('/var/www/public/upload', $_ENV['VAR6']);
    }

    public function testVariablesNotFound()
    {
        $this->expectExceptionMessage('Not set variable ${BAZ}.');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid/.notfoundvars');
        $dotenv->loadEnv();
    }

    public function testCastType()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/2/.env');
        $dotenv->setCastType(true);
        $dotenv->loadEnv();

        $this->assertSame("*int 42", $_ENV['VAR_1']);
        $this->assertSame(2, $_ENV['VAR_1_1']);
        $this->assertSame("*int              4", $_ENV['VAR_1_2']);
        $this->assertSame('*int not digit', $_ENV['VAR_1_7']);
        $this->assertSame("*string *int", $_ENV['VAR_1_8']);
        $this->assertSame(42, $_ENV['VAR_1_3']);
        $this->assertSame('test *int 5', $_ENV['VAR_1_4']);
        $this->assertSame(0755, $_ENV['VAR_1_5']);
        $this->assertSame(0xA, $_ENV['VAR_1_6']);
        $this->assertSame(true, $_ENV['VAR_2']);
        $this->assertSame(false, $_ENV['VAR_3']);
        $this->assertSame(null, $_ENV['VAR_4']);
        $this->assertSame(3.14, $_ENV['VAR_5']);
        $this->assertSame(3.14, $_ENV['VAR_6']);
        $this->assertSame(3.14, $_ENV['VAR_7']);
        $this->assertSame(3.14, $_ENV['VAR_7_1']);
        $this->assertSame('3.14', $_ENV['VAR_7_2']);
        $this->assertSame('', $_ENV['VAR_8']);
    }

    public function testAutoCastType()
    {

        $dotenv = new Dotenv(__DIR__ . '/fixtures/2/.auto_cast_type');
        $dotenv->setCastType(true);
        $dotenv->loadEnv();

        $this->assertSame(42, $_ENV['VAR_1']);
        $this->assertSame("42", $_ENV['VAR_2']);
        $this->assertSame("0755", $_ENV['VAR_3']);
        $this->assertSame("0xA", $_ENV['VAR_4']);
        $this->assertSame(true, $_ENV['VAR_5']);
        $this->assertSame(false, $_ENV['VAR_6']);
        $this->assertSame("", $_ENV['VAR_7']);
        $this->assertSame(null, $_ENV['VAR_8']);
        $this->assertSame(3.14, $_ENV['VAR_9']);
        $this->assertSame(3.14, $_ENV['VAR_10']);
        $this->assertSame("3.14", $_ENV['VAR_11']);
        $this->assertSame("true", $_ENV['VAR_12']);
        $this->assertSame("false", $_ENV['VAR_13']);
        $this->assertSame($_ENV['VAR_5'], $_ENV['VAR_14']);
        $this->assertSame($_ENV['VAR_6'], $_ENV['VAR_15']);
        $this->assertSame($_ENV['VAR_1'], $_ENV['VAR_16']);
        $this->assertSame($_ENV['VAR_10'], $_ENV['VAR_17']);
    }

    public function testEnvWithEq()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3/.env.dist');
        $dotenv->loadEnv();
        $this->assertSame('foo bar = zed', $_ENV['VAR']);
    }

    public function testUsePutEnvTrue()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3/.env.dist');
        $dotenv->loadEnv(true);
        $this->assertSame('foo bar = zed', getenv('VAR'));

        putenv('VAR'); //unset
    }

    public function testUsePutEnvFalse()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3/.env.dist');
        $dotenv->loadEnv();
        $this->assertSame(false, getenv('VAR'));
    }

    public function testNotOverriding()
    {
        $php_version = 'test';
        putenv("PHP_VERSION=$php_version");
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3/.env.dist');
        $dotenv->loadEnv();
        $this->assertSame($php_version, $_ENV['PHP_VERSION']);

        putenv('PHP_VERSION'); //unset
    }

    public function testWithComment()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/with_comment/.env');
        $dotenv->setCastType(true);
        $dotenv->loadEnv();
        $this->assertSame(false, $_ENV['MY_VAR'] ?? false);
        $this->assertSame('      # 23', $_ENV['VAR2']);
        $this->assertSame('/var/www', $_ENV['VAR3']);
        $this->assertSame(null, $_ENV['VAR4']);
    }

    public function testInvalidKey()
    {
        $this->expectException(\Enjoys\Dotenv\Exception\InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid/.error.key');
        $dotenv->loadEnv();
    }



    public function testReplaceValueInEnvFilesDefinedInPutenvOrExport()
    {
        putenv('SYS_VAR=123');
        putenv('SYS_VAR2=456');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/without_defined_vars/.env');
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
        $dotenv = new Dotenv(__DIR__ . '/fixtures/without_defined_vars/.env');
        $dotenv->loadEnv();
        $this->assertSame('987', $_ENV['VAR4']);
        putenv('SYS_VAR');
        putenv('SYS_VAR2');
        putenv('DEFINED_VAR');
    }

    public function testQuotes()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/with_qoutes_and_escaping/.quotes');
        $dotenv->loadEnv();
        $this->assertSame("value in double quotes", $_ENV['VAR1']);
        $this->assertSame('value without quotes', $_ENV['VAR2']);
        $this->assertSame('value in single quotes', $_ENV['VAR3']);
        $this->assertSame('va"', $_ENV['VAR4']);
        $this->assertSame('va" lue"', $_ENV['VAR5']);
        $this->assertSame("\#\"\\", $_ENV['VAR6']);
        $this->assertSame("it\'s single quote", $_ENV['VAR7']);
        $this->assertSame("it's double quote", $_ENV['VAR8']);
    }

    public function testSlashes()
    {
        putenv('VAR=test\"val');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/with_qoutes_and_escaping/.slashes');
        $dotenv->loadEnv();
        $this->assertSame('double quote in middle " text', $_ENV['VAR1']);
        $this->assertSame("value. it\'s var #2", $_ENV['VAR2']);
        $this->assertSame("\\\\ - two backslashes. not's 4", $_ENV['VAR3']);
        $this->assertSame('test\"val', $_ENV['VAR4']);
        $this->assertSame($_ENV['VAR1'], $_ENV['VAR5']);
        $this->assertSame($_ENV['VAR4'], $_ENV['VAR']);
        putenv('VAR');
    }

    public function testEnvArrayAndEnvRawArray()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/1/.env');
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
        ], $dotenv->getEnvCollection()->getCollection());
    }

    public function testAutoCastAndEnablePutEnv()
    {

        $dotenv = new Dotenv(
            __DIR__ . '/fixtures/2/.auto_cast_type'
        );
        $dotenv->setCastType(true);
        $dotenv->loadEnv(true);
        $this->assertSame('42', getenv('VAR_1'));
        $this->assertSame('3.14', getenv('VAR_10'));
        $this->assertSame('true', getenv('VAR_5'));
        $this->assertSame('false', getenv('VAR_6'));
        $this->assertSame('', getenv('VAR_8'));
    }

    public function testMultiline()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/multiline/.env');
        $dotenv->loadEnv(true);

        $this->assertSame(
            <<<ENV
1
2
ENV
            ,
            $_ENV['VAR_MULTILINE']
        );
        $this->assertSame(
            <<<ENV
1
2
ENV
            ,
            getenv('VAR_MULTILINE')
        );
        $this->assertSame('1\n2', $_ENV['VAR_NON_MULTILINE']);
        $this->assertSame('1\n2', getenv('VAR_NON_MULTILINE'));
        $this->assertSame(
            <<<ENV
A
B
C=
ENV
            ,
            $_ENV['VAR_MULTILINE2']
        );
        $this->assertSame(
            <<<ENV
A
B
C=
ENV,
            getenv('VAR_MULTILINE2')
        );
    }

    public function testMultiline1()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/multiline/1');
        $dotenv->loadEnv();
        $this->assertSame(
            <<<ENV
Г
Д
Ж=
ENV
            ,
            $_ENV['VAR']
        );
    }


    public function testMultiline2()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/multiline/2');
        $dotenv->loadEnv();
        $this->assertSame(
            <<<ENV
A
B
C=

ENV
            ,
            $_ENV['VAR']
        );
    }


    public function testMultiline3()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/multiline/3');
        $dotenv->loadEnv();
        $this->assertSame(
            <<<ENV
A
B
C
ENV
            ,
            $_ENV['VAR']
        );
    }
    public function testMultilineInvalid()
    {
        $this->expectException(\Enjoys\Dotenv\Exception\InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/multiline/.invalid');
        $dotenv->loadEnv();
    }

    public function testClear()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/1/.env');
        $dotenv->loadEnv(true);
        $enjoysDotenvArray = $dotenv->getEnvCollection()->getKeys();
        $this->assertSame(implode(",", $enjoysDotenvArray), $_ENV['ENJOYS_DOTENV']);
        $this->assertSame(implode(",", $enjoysDotenvArray), getenv('ENJOYS_DOTENV'));
        Dotenv::clear();
        foreach ($enjoysDotenvArray as $key) {
            $this->assertFalse(getenv($key));
            $this->assertFalse($_ENV[$key] ?? false);
        }
    }

    public function testLoadAppEnvFile()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/loadAppEnvFile/.env');
        $dotenv->loadEnv();
        $this->assertSame('loaded .env.true', $_ENV['LOADED']);
    }

    public function testNestedManyTimesAppEnvFromFiles()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/nestedAppEnv/.env');
        $dotenv->loadEnv();
        $this->assertCount(6, $dotenv->getLoadedPaths());
        $this->assertSame('5', $_ENV['VAR']);
        $this->assertSame([
            'APP_ENV' => '5',
            'VAR' => '5',
        ], $dotenv->getEnvCollection()->getCollection());
        $this->assertSame([
            'APP_ENV' => '5',
            'VAR' => '5',
        ], $dotenv->getEnvRawArray());
    }

    public function testNestedManyTimesAppEnvFromGetEnv()
    {
        putenv('APP_ENV=3');
        $dotenv = new Dotenv(__DIR__.'/fixtures/nestedAppEnv/.env');
        $dotenv->loadEnv();
        $this->assertCount(2, $dotenv->getLoadedPaths());
        $this->assertSame([
            'APP_ENV' => '3',
            'VAR' => '3',
        ], $dotenv->getEnvCollection()->getCollection());
        $this->assertSame([
            'APP_ENV' => '4',
            'VAR' => '3',
        ], $dotenv->getEnvRawArray());
        $this->assertSame('3', $_ENV['VAR']);
    }

    public function testOtherImplementationStorage()
    {
        $storage = $this->getMockForAbstractClass(\Enjoys\Dotenv\StorageInterface::class);
        $dotenv = new Dotenv(__DIR__.'/fixtures/1/.env', storage: $storage);
        $dotenv->loadEnv();
        $this->assertSame([], $dotenv->getLoadedPaths());
    }

    public function testOtherImplementationParser()
    {
        $parser = $this->getMockForAbstractClass(\Enjoys\Dotenv\Parser\ParserInterface::class);
        $dotenv = new Dotenv(__DIR__.'/fixtures/1/.env', parser: $parser);
        $dotenv->loadEnv();
        $this->assertSame([], $dotenv->getEnvCollection()->getCollection());
    }
}
