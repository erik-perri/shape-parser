<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @extends BaseParser<int>
 */
final readonly class IntegerParser extends BaseParser
{
    public function describe(): string
    {
        return 'int';
    }

    public function parse(mixed $data): int
    {
        if (!is_int($data)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        return $data;
    }
}
