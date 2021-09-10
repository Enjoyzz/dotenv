<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class DotenvTest extends TestCase
{

    public function testStarted()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/1');
        $dotenv->loadEnv();

        $this->assertSame('dev', $_ENV['APP_ENV']);
        $this->assertSame('C:/openserver/test', $_ENV['TEST_DIR']);

    }

    public function testCastType()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/2');
        $dotenv->loadEnv();

        $this->assertSame(42, $_ENV['VAR_1']);
        $this->assertSame(true, $_ENV['VAR_2']);
        $this->assertSame(false, $_ENV['VAR_3']);
        $this->assertSame(null, $_ENV['VAR_4']);
        $this->assertSame(3.14, $_ENV['VAR_5']);
        $this->assertSame(3.14, $_ENV['VAR_6']);
        $this->assertSame('3,14', $_ENV['VAR_7']);
        $this->assertSame('', $_ENV['VAR_8']);


    }
}
