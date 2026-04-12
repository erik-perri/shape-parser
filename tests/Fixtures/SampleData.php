<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Fixtures;

final readonly class SampleData
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
        //
    }
}
