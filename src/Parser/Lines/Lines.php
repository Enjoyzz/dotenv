<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Lines;


final class Lines
{

    private array $output = [];

    private function __construct(private array $rawLines)
    {
    }

    public static function handle(array $rawLines): array
    {
        $linesHandler = new self($rawLines);
        $linesHandler->process();
        return $linesHandler->getOutput();
    }


    private function getOutput(): array
    {
        return $this->output;
    }

    private function process(): void
    {
        $multiline = false;
        $multilineBuffer = [];

        foreach ($this->rawLines as $line) {
            [$multiline, $line, $multilineBuffer] = $this->multilineProcess($multiline, $line, $multilineBuffer);

            if (!$multiline) {

                $this->output[] = $line;
            }
        }

    }

    private function multilineProcess(mixed $multiline, mixed $line, mixed $multilineBuffer)
    {
        if ($started = $this->looksLikeMultilineStart($line)) {
            $multiline = true;
        }

        if ($multiline) {
            \array_push($multilineBuffer, $line);

            if ($this->looksLikeMultilineStop($line, $started)) {

                $multiline = false;
                $line = \implode("\\n", $multilineBuffer);
                $multilineBuffer = [];
            }
        }

        return [$multiline, $line, $multilineBuffer];
    }

    private function looksLikeMultilineStart(mixed $line)
    {

        if (\mb_strpos($line, '="', 0, 'UTF-8') && !$this->looksLikeMultilineStop($line, true)){
            return true;
        }
        return false;
    }

    private function looksLikeMultilineStop(string $line, bool $started)
    {
        if ($line === '"') {
            return true;
        }

        $result = \preg_match_all('/(?=([^\\\\]"))/', \str_replace('\\\\', '', $line));
        return $started ? $result > 1 : $result >= 1;
    }

}
