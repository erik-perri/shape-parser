<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeNullable;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use Sourcetoad\ShapeParser\Parsers\Traits\HasFallback;
use Sourcetoad\ShapeParser\Parsers\Traits\HasLenient;
use Sourcetoad\ShapeParser\Parsers\Traits\HasNullable;
use Sourcetoad\ShapeParser\Parsers\Traits\HasOptional;
use Sourcetoad\ShapeParser\Parsers\Traits\HasTransformed;

/**
 * @extends BaseParser<bool>
 */
final readonly class BooleanParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    /** @use HasFallback<bool> */
    use HasFallback;

    /** @use HasLenient<bool> */
    use HasLenient;

    /** @use HasNullable<bool> */
    use HasNullable;

    /** @use HasOptional<bool> */
    use HasOptional;

    /** @use HasTransformed<bool> */
    use HasTransformed;

    public function describe(): string
    {
        return 'bool';
    }

    public function parse(mixed $data): bool
    {
        if (! is_bool($data)) {
            throw ParseException::fromMessage(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        return $data;
    }
}
