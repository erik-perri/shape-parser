<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use LogicException;
use Sourcetoad\ShapeParser\ParserContract;

/**
 * @template T
 *
 * @extends BaseParser<T|null>
 */
final readonly class NullableParser extends BaseParser
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
        return sprintf('nullable<%s>', $this->parser->describe());
    }

    public function parse(mixed $data): mixed
    {
        if ($data === null) {
            return null;
        }

        return $this->parser->parse($data);
    }

    public function nullable(): never
    {
        throw new LogicException('Cannot call nullable() on an already nullable parser.');
    }
}
