<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use Sourcetoad\ShapeParser\Parsers\Traits\HasFallback;
use Sourcetoad\ShapeParser\Parsers\Traits\HasLenient;
use Sourcetoad\ShapeParser\Parsers\Traits\HasOptional;
use Sourcetoad\ShapeParser\Parsers\Traits\HasTransformed;

/**
 * @template T
 *
 * @extends BaseParser<T|null>
 */
final readonly class NullableParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeOptional, CanBeTransformed
{
    /** @use HasFallback<T|null> */
    use HasFallback;

    /** @use HasLenient<T|null> */
    use HasLenient;

    /** @use HasOptional<T|null> */
    use HasOptional;

    /** @use HasTransformed<T|null> */
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
        return sprintf('nullable<%s>', $this->parser->describe());
    }

    public function parse(mixed $data): mixed
    {
        if ($data === null) {
            return null;
        }

        return $this->parser->parse($data);
    }

    public function isOptional(): bool
    {
        return $this->parser instanceof BaseParser && $this->parser->isOptional();
    }
}
