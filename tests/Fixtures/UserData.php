<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Fixtures;

final readonly class UserData
{
    public function __construct(
        public int $userId,
        public string $firstName,
        public string $createdAt,
    ) {
        //
    }
}
