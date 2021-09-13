<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class DotenvTest extends TestCase
{
    use \Enjoys\Traits\Reflection;

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
        $this->expectException(InvalidArgumentException::class);
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

        //unset
        putenv('VAR');
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
        $this->assertSame('# 23', $_ENV['VAR2']);
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

}
