<?php

declare(strict_types=1);

namespace Parser;

use Enjoys\Dotenv\Parser\Builder;
use Enjoys\Dotenv\Parser\Parser;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private string $input = <<<ENV
# Comment
VAR1 = value1
VAR2 = value 2

VAR3 = "value #3" comment
VAR4 = value #3 comment
VAR5='value \'5'
VAR6="value \"6"           #comment
ENV;

    private string $expect = <<<ENV
# Comment
VAR1=value1
VAR2=value 2

VAR3="value #3" #comment
VAR4=value #3 comment
VAR5="value \'5"
VAR6="value \"6" #comment
ENV;

    public function testBuild()
    {
        $parser = new Parser();
        $parser->parse($this->input);

        $builder = new Builder($parser->getLines());
        $this->assertSame($this->expect, $builder->build());
    }
}
