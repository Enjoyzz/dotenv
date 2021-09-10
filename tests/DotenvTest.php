<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class DotenvTest extends TestCase
{

    public function testStarted()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures');
        $dotenv->loadEnv();

        var_dump($_ENV);
    }
}
