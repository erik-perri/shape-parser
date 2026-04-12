<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use LogicException;
use Sourcetoad\ShapeParser\ParserContract;

/**
 * @template T
 * @extends BaseParser<T>
 */
final readonly class OptionalParser extends BaseParser
{
    /**
     * @param ParserContract<T> $parser
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

    public function optional(): never
    {
        throw new LogicException('Cannot call optional() on an already optional parser.');
    }

    public function nullable(): never
    {
        throw new LogicException('Cannot call nullable() on an optional parser.');
    }

    public function lenient(): never
    {
        throw new LogicException('Cannot call lenient() on an optional parser.');
    }

    public function transform(callable $fn): never
    {
        throw new LogicException('Cannot call transform() on an optional parser.');
    }
}
