<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @extends BaseParser<bool>
 */
final readonly class BooleanParser extends BaseParser
{
    public function describe(): string
    {
        return 'bool';
    }

    public function parse(mixed $data): bool
    {
        if (!is_bool($data)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        return $data;
    }
}
