<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @extends BaseParser<int>
 */
final readonly class IntegerParser extends BaseParser
{
    public function parse(mixed $data): int
    {
        if (!is_int($data)) {
            throw new ParseException("Expected int, got " . get_debug_type($data));
        }

        return $data;
    }
}
