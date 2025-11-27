<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser;

use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

class ShapeFactory
{
    /**
     * @template Tk of array-key
     * @template Tv
     * @param array<Tk, ParserContract<Tv>> $shape
     * @return ObjectParser<Tk, Tv>
     */
    public function object(array $shape): ObjectParser
    {
        return new ObjectParser($shape);
    }

    public function string(): StringParser
    {
        return new StringParser();
    }

    public function integer(): IntegerParser
    {
        return new IntegerParser();
    }
}
