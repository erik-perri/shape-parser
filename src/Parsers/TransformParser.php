<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Closure;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;

/**
 * @template TIn
 * @template TOut
 * @extends BaseParser<TOut>
 */
final readonly class TransformParser extends BaseParser
{
    /**
     * @param ParserContract<TIn> $parser
     * @param Closure(TIn): TOut $fn
     */
    public function __construct(
        public ParserContract $parser,
        private Closure $fn,
    ) {
        //
    }

    public function describe(): string
    {
        return sprintf('transform<%s>', $this->parser->describe());
    }

    /**
     * @return TOut
     * @throws ParseException
     */
    public function parse(mixed $data): mixed
    {
        // Only ParseException thrown by the inner parser is caught by lenient().
        // Any exception thrown by the closure propagates unchanged.
        return ($this->fn)($this->parser->parse($data));
    }
}
