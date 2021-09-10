<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


class Dotenv
{
    private string $dirname;

    private array $envArray = [];

    public function __construct(
        string $dirname,
        private string $distEnvFilename = '.env.dist',
        private string $envFilename = '.env',
        //private array $suffixes = ['local', 'dev', 'test']
    )
    {
        $this->dirname = rtrim($dirname) . DIRECTORY_SEPARATOR;
    }

    public function loadEnv(bool $usePutEnv = false)
    {
        $paths = $this->getPaths();
        $this->doMerge($paths);
        $this->doLoad($usePutEnv);
    }

    private function getPaths()
    {
        $paths = [
            realpath($this->getDirname() . $this->getDistEnvFilename()),
            realpath($this->getDirname() . $this->getEnvFilename())
        ];

//        foreach ($this->getSuffixes() as $suffix) {
//            $paths[] = realpath($this->getDirname() . $this->getEnvFilename() . '.' . $suffix);
//        }

        return array_filter($paths, function ($item) {
            return is_string($item);
        });
    }

    public function getDirname(): string
    {
        return $this->dirname;
    }

    public function getDistEnvFilename(): string
    {
        return $this->distEnvFilename;
    }

    public function getLocalSuffix(): string
    {
        return $this->localSuffix;
    }

    public function getEnvFilename(): string
    {
        return $this->envFilename;
    }

    public function getSuffixes(): array
    {
        return $this->suffixes;
    }

    private function doMerge(array $array)
    {
        foreach ($array as $item) {
            $this->envArray = array_merge($this->envArray, $this->getArrayData($item));
        }
    }

    private function getArrayData(string $string): array
    {
        $result = [];
        $data = $this->doRead($string);
        foreach ($this->parseToArray($data) as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

    private function doRead(string $string): string
    {
        return str_replace(["\r\n", "\r"], "\n", file_get_contents($string));
    }

    private function parseToArray($input): \Generator
    {
        foreach (explode("\n", $input) as $line) {
            $fields = explode('=', $line);
            if (count($fields) == 2) {
                list($key, $value) = $fields;
                yield \trim($key) => \trim($value);
            }
        }
    }

    private function doLoad(bool $usePutEnv = false)
    {
        foreach ($this->envArray as $key => $value) {
            if ($usePutEnv === true) {
                putenv("{$key}={$value}");
            }
            $_ENV[$key] = $value;
        }
    }
}