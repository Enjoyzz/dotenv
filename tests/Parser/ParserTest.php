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
        if ($expect === false){
            $this->expectException(InvalidArgumentException::class);
        }
        $parser  = new Parser($input);
        $this->assertSame($expect, $parser->getRawLinesArray());
    }

    public function dataForTestSplitContentOnRawArray(): array
    {
        return [
            ["", ['']],
            ["1", ['1']],
            ["1    \r\n    2  ", ['1', '2']],
            ["1\r2", ['1', '2']],
            ["1\r2", ['1', '2']],
            ["1\x0b2", ['1', '2']],
            ["1\f2", ['1', '2']],
            ["1\x852", ['1', '2']],
        ];
    }


    public function testParse()
    {
        $parser  = new Parser(<<<ENV
# comment

VAR1 = value  1
VAR2=value2#comment
VAR3 = "     value3 #not comment"                comment
ENV
);
        $parser->parse();
        $this->assertSame([
            'VAR1' => 'value  1',
            'VAR2' => 'value2',
            'VAR3' => '     value3 #not comment',
        ], $parser->getEnvArray());
    }


}
