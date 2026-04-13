<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeNullable;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;

/**
 * @template T
 *
 * @extends BaseParser<T>
 */
final readonly class OptionalParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeTransformed
{
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
