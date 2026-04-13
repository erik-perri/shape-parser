<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeNullable;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;

/**
 * @extends BaseParser<float>
 */
final readonly class FloatParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    public function describe(): string
    {
        return 'float';
    }

    public function parse(mixed $data): float
    {
        if (! is_float($data)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        return $data;
    }
}
