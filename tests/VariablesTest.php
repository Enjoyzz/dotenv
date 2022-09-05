<?php

declare(strict_types=1);


use Enjoys\Dotenv\Dotenv;
use Enjoys\Dotenv\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class VariablesTest extends TestCase
{
    protected function setUp(): void
    {
        Dotenv::clear();
    }

    public function testDefinedVariables()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/variables', '1');
        $dotenv->loadEnv();
        $this->assertSame($_ENV['VAR1'], $_ENV['VAR2']);
    }

    public function testNotDefinedVariablesWithEq()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/variables', '2');
        $dotenv->loadEnv();
        $this->assertTrue($_ENV['VAR'] === $_ENV['VAR1']);
        $this->assertTrue($_ENV['VAR2'] === $_ENV['VAR1']);
    }

    public function testNotDefinedVariablesWithMinus()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/variables', '3');
        $dotenv->loadEnv();
        $this->assertSame('default', $_ENV['VAR1']);
        $this->assertFalse($_ENV['VAR'] ?? false);
    }

    public function testNotDefinedVariablesWithEqAndCast()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/variables', '4');
        $dotenv->loadEnv();
        $this->assertSame(true, $_ENV['VAR1']);
        $this->assertTrue($_ENV['VAR']);
        $this->assertSame(42, $_ENV['VAR2']);
        $this->assertTrue($_ENV['VAR2'] === $_ENV['VAR3']);
    }

    public function testNotDefinedVariablesWithQuestin()
    {
        $this->expectException(InvalidArgumentException::class);
        $dotenv = new Dotenv(__DIR__.'/fixtures/variables', '5');
        $dotenv->loadEnv();
    }

    public function testNotDefinedVariables()
    {
        $dotenv = new Dotenv(__DIR__.'/fixtures/variables', '6');
        $dotenv->loadEnv();
        $this->assertSame('/path', $_ENV['VAR1']);
    }
}
