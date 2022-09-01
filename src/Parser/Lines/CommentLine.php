<?php

declare(strict_types=1);


namespace Enjoys\Dotenv\Parser\Lines;


final class CommentLine implements LineInterface
{
    private string $comment;

    public function __construct(string $comment)
    {
        $this->comment = ltrim($comment, "#");
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
