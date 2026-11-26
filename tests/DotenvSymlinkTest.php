<?php

declare(strict_types=1);

use Enjoys\Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

final class DotenvSymlinkTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        if (!function_exists('symlink')) {
            $this->markTestSkipped('symlink() недоступен');
        }

        $this->root = sys_get_temp_dir() . '/dotenv_' . bin2hex(random_bytes(6));
        mkdir($this->root . '/shared', 0777, true);
        mkdir($this->root . '/releases/1', 0777, true);
        putenv('APP_ENV');
    }

    protected function tearDown(): void
    {
        putenv('APP_ENV');
        $this->removeDir($this->root);
        Dotenv::clear();
    }

    public function testSymlinkedEnvOutsideReleaseDirIsLoaded(): void
    {
        file_put_contents($this->root . '/shared/.env', "FOO=bar\n");
        symlink($this->root . '/shared/.env', $this->root . '/releases/1/.env');

        $dotenv = new Dotenv($this->root . '/releases/1/.env');
        $dotenv->loadEnv();

        self::assertSame('bar', $dotenv->getEnvCollection()->get('FOO'));
    }

    public function testSymlinkedDistFileIsLoaded(): void
    {
        file_put_contents($this->root . '/shared/.env.dist', "FOO=dist\n");
        symlink($this->root . '/shared/.env.dist', $this->root . '/releases/1/.env.dist');

        $dotenv = new Dotenv($this->root . '/releases/1/.env');
        $dotenv->loadEnv();

        self::assertSame('dist', $dotenv->getEnvCollection()->get('FOO'));
    }

    public function testSymlinkedAppEnvFileIsLoadedAndOverrides(): void
    {
        file_put_contents($this->root . '/shared/.env', "APP_ENV=prod\nFOO=base\n");
        file_put_contents($this->root . '/shared/.env.prod', "FOO=prod\n");
        symlink($this->root . '/shared/.env', $this->root . '/releases/1/.env');
        symlink($this->root . '/shared/.env.prod', $this->root . '/releases/1/.env.prod');

        $dotenv = new Dotenv($this->root . '/releases/1/.env');
        $dotenv->loadEnv();

        self::assertSame('prod', $dotenv->getEnvCollection()->get('FOO'));
    }

    public function testAppEnvFromProcessEnvWithSymlink(): void
    {
        putenv('APP_ENV=staging');
        file_put_contents($this->root . '/shared/.env', "BASE=1\n");
        file_put_contents($this->root . '/shared/.env.staging', "FOO=staging\n");
        symlink($this->root . '/shared/.env', $this->root . '/releases/1/.env');
        symlink($this->root . '/shared/.env.staging', $this->root . '/releases/1/.env.staging');

        $dotenv = new Dotenv($this->root . '/releases/1/.env');
        $dotenv->loadEnv();

        self::assertSame('staging', $dotenv->getEnvCollection()->get('FOO'));
    }

    public function testSymlinkedDirectoryIsLoaded(): void
    {
        file_put_contents($this->root . '/releases/1/.env', "FOO=bar\n");
        symlink($this->root . '/releases/1', $this->root . '/current');

        $dotenv = new Dotenv($this->root . '/current/.env');
        $dotenv->loadEnv();

        self::assertSame('bar', $dotenv->getEnvCollection()->get('FOO'));
    }

    public function testBrokenSymlinkDoesNotThrow(): void
    {
        symlink($this->root . '/shared/missing', $this->root . '/releases/1/.env');

        $dotenv = new Dotenv($this->root . '/releases/1/.env');
        $dotenv->loadEnv();

        self::assertNull($dotenv->getEnvCollection()->get('FOO'));
    }

    public function testAppEnvPathTraversalIsRejected(): void
    {
        putenv('APP_ENV=../../shared/.env');
        file_put_contents($this->root . '/releases/1/.env', "BASE=1\n");

        $this->expectException(\InvalidArgumentException::class);

        $dotenv = new Dotenv($this->root . '/releases/1/.env');
        $dotenv->loadEnv(true);
    }

    public function testUrlWrapperIsNotRead(): void
    {
        $dotenv = new Dotenv('http://127.0.0.1:1/.env');
        $dotenv->loadEnv();

        self::assertNull($dotenv->getEnvCollection()->get('FOO'));
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir) || is_link($dir)) {
            is_link($dir) || is_file($dir) ? unlink($dir) : null;
            return;
        }

        foreach (scandir($dir) ?: [] as $item) {
            if ($item !== '.' && $item !== '..') {
                $this->removeDir($dir . '/' . $item);
            }
        }

        rmdir($dir);
    }
}
