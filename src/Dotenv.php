<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


use Webmozart\Assert\Assert;

class Dotenv
{
    private string $baseDirectory;
    /**
     * @var string[]
     */
    private array $envArray = [];
    private string $distEnvFilename;
    private string $envFilename;

    public function __construct(
        string $baseDirectory,
        string $envFilename = '.env',
        string $distEnvFilename = '.env.dist'
    ) {
        $this->baseDirectory = rtrim($baseDirectory, "/") . DIRECTORY_SEPARATOR;
        $this->distEnvFilename = $distEnvFilename;
        $this->envFilename = $envFilename;
    }

    public function loadEnv(bool $usePutEnv = false): void
    {
        $this->doMerge($this->getGeneralPaths());
        $this->doMerge($this->getExtraPaths());
        $this->doLoad($usePutEnv);
    }

    /**
     * @return string[]
     */
    private function getExtraPaths(): array
    {
        $env = $this->envArray['APP_ENV'] ?? '';

        if ($env === '') {
            return [];
        }
        $path = realpath($this->baseDirectory . $this->envFilename . '.' . $env);

        if ($path === false) {
            return [];
        }

        return [$path];
    }

    /**
     * @return string[]
     */
    private function getGeneralPaths(): array
    {
        $paths = [
            realpath($this->baseDirectory . $this->distEnvFilename),
            realpath($this->baseDirectory . $this->envFilename)
        ];

        return array_filter($paths, function ($item) {
            return is_string($item);
        });
    }

    /**
     * @param string[] $array
     */
    private function doMerge(array $array): void
    {
        foreach ($array as $path) {
            $this->envArray = array_merge($this->envArray, $this->getArrayData($path));
        }
    }

    /**
     * @return string[]
     */
    private function getArrayData(string $path): array
    {
        $result = [];

        $data = file_get_contents($path);

        /**
         * @var string $key
         * @var string $value
         */
        foreach ($this->parseToArray($data) as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }


    private function parseToArray(string $input): \Generator
    {
        foreach (preg_split("/\R/", $input) as $line) {
            $line = trim($line);
            if ($this->isComment($line)) {
                continue;
            }
            $fields = array_map('trim', explode('=', $line, 2));

            if (count($fields) == 2) {
                list($key, $value) = $fields;
                Assert::regex(
                    $key,
                    '/^([A-Z_0-9]+)$/i',
                    'The key %s have invalid chars. The key must have only letters (A-Z) digits (0-9) and _'
                );

                yield $key => $value;
            }
        }
    }


    private function doLoad(bool $usePutEnv): void
    {
        /** @var string $key */
        foreach ($this->envArray as $key => $value) {
            $valueHandler = new ValueHandler($this->envArray);
            $value = $valueHandler->getHandledValue($value);

            if (getenv($key)) {
                $value = getenv($key);
            }

            $_ENV[$key] = $value;

            if (!getenv($key) && $usePutEnv === true) {
                putenv("$key=$value");
            }
        }
    }

    private function isComment(string $line): bool
    {
        return (bool)preg_match('/^#/', $line);
    }


}