<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class DotenvTest extends TestCase
{

    public function testVariableReplace()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/1');
        $dotenv->loadEnv();

        $this->assertSame('dev', $_ENV['APP_ENV']);
        $this->assertSame('C:/openserver/test', $_ENV['TEST_DIR']);
    }

    public function testCastType()
    {
        $dotenv = new Dotenv(__DIR__ . '/fixtures/2');
        $dotenv->loadEnv();

        $this->assertSame(42, $_ENV['VAR_1']);
        $this->assertSame(42, $_ENV['VAR_1_3']);
        $this->assertSame(true, $_ENV['VAR_2']);
        $this->assertSame(false, $_ENV['VAR_3']);
        $this->assertSame(null, $_ENV['VAR_4']);
        $this->assertSame(3.14, $_ENV['VAR_5']);
        $this->assertSame(3.14, $_ENV['VAR_6']);
        $this->assertSame('3,14', $_ENV['VAR_7']);
        $this->assertSame(3.14, $_ENV['VAR_7_1']);
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
        $php_version = getenv('PHP_VERSION');
        $dotenv = new Dotenv(__DIR__ . '/fixtures/3');
        $dotenv->loadEnv();
        $this->assertSame($php_version, $_ENV['PHP_VERSION']);
    }

    public function testInvalidInt()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.env.dist', '.error.int');
        $dotenv->loadEnv();

    }

    public function testInvalidFloat()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.env.dist', '.error.float');
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
        $dotenv = new Dotenv(__DIR__ . '/fixtures/invalid', '.env.dist', '.error.key');
        $dotenv->loadEnv();
    }

}
