<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;

/**
 * @template T
 * @extends BaseParser<T>
 */
final readonly class UnionParser extends BaseParser
{
    /**
     * @var array<int, ParserContract<T>> $parsers
     */
    private array $parsers;

    /**
     * @param ParserContract<T> ...$parser
     */
    public function __construct(ParserContract ...$parser)
    {
        $this->parsers = array_values($parser);
    }

    public function describe(): string
    {
        return implode('|', array_map(fn (ParserContract $parser) => $parser->describe(), $this->parsers));
    }

    public function parse(mixed $data): mixed
    {
        foreach ($this->parsers as $parser) {
            $result = $parser->safeParse($data);

            if ($result->success) {
                // @phpstan-ignore return.type
                return $result->data;
            }
        }

        throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
    }
}
