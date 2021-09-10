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
}
