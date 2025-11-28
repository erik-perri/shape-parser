<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser;

use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\RecordParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;
use Sourcetoad\ShapeParser\Parsers\UnionParser;

class ShapeFactory
{
    public function integer(): IntegerParser
    {
        return new IntegerParser();
    }

    /**
     * @template T
     * @param ParserContract<T> $parser
     * @return ListParser<T>
     */
    public function list(ParserContract $parser): ListParser
    {
        return new ListParser($parser);
    }

    /**
     * @param array<string, ParserContract<mixed>> $shape
     * @return ObjectParser<array<array-key, mixed>>
     */
    public function object(array $shape): ObjectParser
    {
        return new ObjectParser($shape);
    }

    /**
     * @template K of array-key
     * @template T
     * @param ParserContract<K> $keyParser
     * @param ParserContract<T> $valueParser
     * @return RecordParser<K, T>
     */
    public function record(ParserContract $keyParser, ParserContract $valueParser): RecordParser
    {
        return new RecordParser($keyParser, $valueParser);
    }

    public function string(): StringParser
    {
        return new StringParser();
    }

    /**
     * @template T
     * @param ParserContract<T> ...$parsers
     * @return UnionParser<T>
     */
    public function union(ParserContract ...$parsers): UnionParser
    {
        return new UnionParser(...$parsers);
    }
}
