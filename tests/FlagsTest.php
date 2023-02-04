<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

final class FlagsTest extends TestCase
{

    public function testFlag_CAST_TYPE_ENV_VALUE()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/2/.env', null, null, Dotenv::CAST_TYPE_ENV_VALUE);
        $dotenv->loadEnv();
        $this->assertSame(42, $_ENV['VAR_1_3']);
    }

    public function testDisableCastType()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/2/.env', null, null, Dotenv::CAST_TYPE_ENV_VALUE);
        $dotenv->disableCastType();
        $dotenv->loadEnv();
        $this->assertSame("42", $_ENV['VAR_1_3']);
    }

    public function testFlag_CLEAR_MEMORY_AFTER_LOAD_ENV()
    {
        $this->expectErrorMessage('Typed property Enjoys\Dotenv\Dotenv::$envCollection must not be accessed before initialization');
        $dotenv = new Dotenv(__DIR__.'/fixtures/2/.env', null, null,  Dotenv::CLEAR_MEMORY_AFTER_LOAD_ENV);
        $dotenv->loadEnv();
        $result = $dotenv->getEnvCollection();
    }

    public function testFlag_POPULATE_SERVER()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/2/.env', null, null,  Dotenv::POPULATE_SERVER|Dotenv::CAST_TYPE_ENV_VALUE);
        $dotenv->loadEnv();
        $this->assertSame(42, $_SERVER['VAR_1_3']);
    }

    public function testFlag_POPULATE_PUTENV()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/2/.env', null, null,  Dotenv::POPULATE_PUTENV|Dotenv::CAST_TYPE_ENV_VALUE);
        $dotenv->loadEnv();
        $this->assertSame('42', getenv('VAR_1_3'));
    }
}
