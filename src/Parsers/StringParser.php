<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @extends BaseParser<string>
 */
final readonly class StringParser extends BaseParser
{
    public function describe(): string
    {
        return 'string';
    }

    public function parse(mixed $data): string
    {
        if (!is_string($data)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        return $data;
    }
}
