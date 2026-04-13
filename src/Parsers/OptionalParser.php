<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeNullable;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use Sourcetoad\ShapeParser\Parsers\Traits\HasFallback;
use Sourcetoad\ShapeParser\Parsers\Traits\HasLenient;
use Sourcetoad\ShapeParser\Parsers\Traits\HasNullable;
use Sourcetoad\ShapeParser\Parsers\Traits\HasTransformed;

/**
 * @template T
 *
 * @extends BaseParser<T>
 */
final readonly class OptionalParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeTransformed
{
    /** @use HasFallback<T> */
    use HasFallback;

    /** @use HasLenient<T> */
    use HasLenient;

    /** @use HasNullable<T> */
    use HasNullable;

    /** @use HasTransformed<T> */
    use HasTransformed;

    /**
     * @param  ParserContract<T>  $parser
     */
    public function __construct(
        private ParserContract $parser,
    ) {
        //
    }

    public function describe(): string
    {
        return sprintf('optional<%s>', $this->parser->describe());
    }

    public function parse(mixed $data): mixed
    {
        return $this->parser->parse($data);
    }

    public function isOptional(): bool
    {
        return true;
    }
}
