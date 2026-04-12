<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Fixtures;

final readonly class ContentData
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $url,
    ) {
        //
    }
}
