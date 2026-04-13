<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Data;

final readonly class ParseIssue
{
    /**
     * @param  list<string|int>  $path
     */
    public function __construct(
        public array $path,
        public string $message,
    ) {
        //
    }

    public function withPrefix(string|int $segment): self
    {
        return new self([$segment, ...$this->path], $this->message);
    }
}
