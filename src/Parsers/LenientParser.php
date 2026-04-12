<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use LogicException;
use Sourcetoad\ShapeParser\ParserContract;

/**
 * @template T
 * @extends BaseParser<T|null>
 */
final readonly class LenientParser extends BaseParser
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
        return sprintf('lenient<%s>', $this->parser->describe());
    }

    public function parse(mixed $data): mixed
    {
        $result = $this->parser->safeParse($data);

        return $result->success ? $result->data : null;
    }

    /**
     * @return FallbackParser<T|null>
     */
    public function fallback(mixed $fallback): FallbackParser
    {
        /** @var FallbackParser<T|null> */
        return new FallbackParser($this->parser, $fallback);
    }

    public function lenient(): never
    {
        throw new LogicException('Cannot call lenient() on an already lenient parser.');
    }

    public function nullable(): never
    {
        throw new LogicException('Cannot call nullable() on an already lenient parser.');
    }
}
