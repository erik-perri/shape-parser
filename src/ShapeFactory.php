<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser;

use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

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

    public function string(): StringParser
    {
        return new StringParser();
    }
}
