<?php

declare(strict_types=1);

namespace Parser\Env;

use Enjoys\Dotenv\Parser\Env\Comment;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{

    /**
     * @dataProvider data
     */
    public function testCommentInEnv($value, $expect)
    {
        $comment = new Comment($value);
        $this->assertSame($expect, $comment->getValue());
        $this->assertSame(' #'.$expect, $comment->__toString());
    }

    public function data()
    {
        return [
            ['      #comment', 'comment'],
            ['      comment', 'comment'],
            ['      com #ment', 'com #ment'],
        ];
    }
}
