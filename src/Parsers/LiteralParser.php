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
 * @template T of bool|int|string
 *
 * @extends BaseParser<T>
 */
final readonly class LiteralParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    /** @use HasFallback<T> */
    use HasFallback;

    /** @use HasLenient<T> */
    use HasLenient;

    /** @use HasNullable<T> */
    use HasNullable;

    /** @use HasOptional<T> */
    use HasOptional;

    /** @use HasTransformed<T> */
    use HasTransformed;

    /**
     * @param  T  $literal
     */
    public function __construct(public bool|int|string $literal)
    {
        //
    }

    public function describe(): string
    {
        $value = $this->literal;

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        if (is_int($value)) {
            $value = (string) $value;
        }

        return sprintf('literal(%s)', $value);
    }

    /**
     * @return T
     */
    public function parse(mixed $data): mixed
    {
        if ($data !== $this->literal) {
            throw ParseException::fromMessage(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        return $data;
    }
}
