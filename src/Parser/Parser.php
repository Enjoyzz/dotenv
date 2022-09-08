<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser;


use Enjoys\Dotenv\Parser\Env\Comment;
use Enjoys\Dotenv\Parser\Env\Key;
use Enjoys\Dotenv\Parser\Env\Value;
use Enjoys\Dotenv\Parser\Lines\CommentLine;
use Enjoys\Dotenv\Parser\Lines\EmptyLine;
use Enjoys\Dotenv\Parser\Lines\EnvLine;
use Enjoys\Dotenv\Parser\Lines\LineInterface;
use Enjoys\Dotenv\Parser\Lines\Multiline;

final class Parser implements ParserInterface
{

    /**
     * @param string $content
     * @return array<string, string|null>
     */
    public function parseEnv(string $content): array
    {
        $envArray = [];
        /** @var LineInterface $line */
        foreach ($this->parseLines($content) as $line) {
            if ($line instanceof EnvLine){
                $envArray[$line->getKey()->getValue()] = $line->getValue()?->getValue();
            }
        }
        return $envArray;
    }

    /**
     * @param string $content
     * @return array<array-key, LineInterface>
     */
    public function parseStructure(string $content): array
    {
        $structure = [];
        /** @var LineInterface $line */
        foreach ($this->parseLines($content) as $line) {
            if ($line instanceof EnvLine){
                $structure[$line->getKey()->getValue()] = $line;
                continue;
            }
            $structure[] = $line;
        }
        return $structure;
    }

    public function parseLines(string $content): \Generator
    {
        foreach (Multiline::handle(
            array_map(
                'trim',
                preg_split("/\R/", $content)
            )
        ) as $line) {
            if (empty($line)) {
                yield new EmptyLine();
                continue;
            }

            if (str_starts_with($line, '#')) {
                yield new CommentLine($line);
                continue;
            }

            [$key, $value, $comment] = $this->parseEnvLine($line);
            yield new EnvLine(
                $key,
                $value,
                $comment
            );
        }
    }

    /**
     * @param string $rawLine
     * @return array{0: Key, 1: Value|null, 2: Comment|null}
     */
    private function parseEnvLine(string $rawLine): array
    {
        /**
         * $explodedLine[0] - rawKey
         * $explodedLine[1] - rawValue
         * @var string[] $explodedLine
         */
        $explodedLine = array_map('trim', explode('=', $rawLine, 2));

        return [
            new Key($explodedLine[0]),
            ...$this->parseValue($explodedLine[1] ??= null)
        ];
    }

    /**
     * @param string|null $rawValue
     * @return array<int, Value|Comment|null>
     */
    private function parseValue(?string $rawValue): array
    {
        if ($rawValue === null) {
            return [
                null,
                null
            ];
        }

        preg_match(
            '/^(?<value>([\'"])(?:(?!\1|\\\\).|\\\\.)*\2)(?<comment>.*)?/',
            $rawValue,
            $matches,
            PREG_UNMATCHED_AS_NULL
        );

        $matches['value'] ??= false;
        $matches['comment'] ??= null;

        if ($matches['value']) {
            return [
                new Value($matches['value']),
                $matches['comment'] ? new Comment($matches['comment']) : null
            ];
        }

        $unquotedValue = array_map('trim', explode('#', $rawValue, 2));
        return [
            new Value($unquotedValue[0]),
            ($unquotedValue[1] ?? null) ? new Comment($unquotedValue[1]) : null
        ];
    }

}
