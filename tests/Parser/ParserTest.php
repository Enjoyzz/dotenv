<?php

declare(strict_types=1);

namespace Parser;

use Enjoys\Dotenv\Exception\InvalidArgumentException;
use Enjoys\Dotenv\Parser\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{

    /**
     * @dataProvider dataForTestSplitContentOnRawArray
     */
    public function testSplitContentOnRawArray($input, $expect)
    {
        if ($expect === false) {
            $this->expectException(InvalidArgumentException::class);
        }
        $parser = new Parser();
        $parser->parse($input);
        $this->assertSame($expect, $parser->getRawLinesArray());
    }

    public function dataForTestSplitContentOnRawArray(): array
    {
        return [
            ["", ['']],
            ["A", ['A']],
            ["A    \r\n    B  ", ['A', 'B']],
            ["A\rB", ['A', 'B']],
            ["A\rB", ['A', 'B']],
            ["A\x0bB", ['A', 'B']],
            ["A\fB", ['A', 'B']],
            ["A\x85B", ['A', 'B']],
        ];
    }


    public function testParse()
    {
        $parser = new Parser();
        $parser->parse(
            <<<ENV
# comment

VAR1 = value  1
VAR2=value2#comment
VAR3 = "     value3 #not comment"                comment
ENV
        );
        $this->assertSame([
            'VAR1' => 'value  1',
            'VAR2' => 'value2',
            'VAR3' => '     value3 #not comment',
        ], $parser->getEnvArray());
    }


}
