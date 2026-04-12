<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @extends BaseParser<int|float>
 */
final readonly class NumberParser extends BaseParser
{
    public function describe(): string
    {
        return 'number';
    }

    public function parse(mixed $data): int|float
    {
        if (! is_int($data) && ! is_float($data)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        return $data;
    }
}
