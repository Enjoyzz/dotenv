<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

final class FlagsTest extends TestCase
{

    public function testFlag_CAST_TYPE_ENV_VALUE()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/2/.env', flags: Dotenv::CAST_TYPE_ENV_VALUE);
        $dotenv->loadEnv();
        $this->assertSame(42, $_ENV['VAR_1_3']);
    }

    public function testDisableCastType()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/2/.env', flags: Dotenv::CAST_TYPE_ENV_VALUE);
        $dotenv->disableCastType();
        $dotenv->loadEnv();
        $this->assertSame("42", $_ENV['VAR_1_3']);
    }

    public function testFlag_CLEAR_MEMORY_AFTER_LOAD_ENV()
    {
        $this->expectErrorMessage('Typed property Enjoys\Dotenv\Dotenv::$envCollection must not be accessed before initialization');
        $dotenv = new Dotenv(__DIR__.'/fixtures/2/.env', flags: Dotenv::CLEAR_MEMORY_AFTER_LOAD_ENV);
        $dotenv->loadEnv();
        $result = $dotenv->getEnvCollection();
    }

}
