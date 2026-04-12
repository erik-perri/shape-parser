<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Fixtures;

final readonly class PostData
{
    public function __construct(
        public string $title,
        public UserData $author,
    ) {
        //
    }
}
