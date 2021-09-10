<?php

declare(strict_types=1);


namespace Enjoys\Dotenv;


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
        string $distEnvFilename = '.env.dist',
        string $envFilename = '.env'
    ) {
        $this->baseDirectory = rtrim($baseDirectory) . DIRECTORY_SEPARATOR;
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
        $env = $this->envArray['APP_ENV'] ?? null;

        if ($env === null) {
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
        $data = $this->doRead($path);

        /**
         * @var string $key
         * @var string $value
         */
        foreach ($this->parseToArray($data) as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

    private function doRead(string $path): string
    {
        return str_replace(["\r\n", "\r"], "\n", file_get_contents($path));
    }

    private function parseToArray(string $input): \Generator
    {
        foreach (explode("\n", $input) as $line) {
            $fields = explode('=', $line);
            if (count($fields) == 2) {
                list($key, $value) = $fields;
                yield \trim($key) => \trim($value);
            }
        }
    }

    private function doLoad(bool $usePutEnv = false): void
    {
        /** @var string $key */
        foreach ($this->envArray as $key => $value) {
            $value = preg_replace_callback(
                '/(\${(.+?)})/',
                function (array $matches) {
                    return $this->envArray[(string)$matches[2]] ?? '';
                },
                $value
            );

            if (getenv($key)) {
                $value = getenv($key);
            }

            $_ENV[$key] = $value;

            if (!getenv($key) && $usePutEnv === true) {
                putenv("$key=$value");
            }
        }
    }


}