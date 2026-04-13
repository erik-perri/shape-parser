<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser;

use Sourcetoad\ShapeParser\Parsers\BooleanParser;
use Sourcetoad\ShapeParser\Parsers\DateTimeParser;
use Sourcetoad\ShapeParser\Parsers\DiscriminatedUnionParser;
use Sourcetoad\ShapeParser\Parsers\EnumParser;
use Sourcetoad\ShapeParser\Parsers\FloatParser;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\LiteralParser;
use Sourcetoad\ShapeParser\Parsers\NumberParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\RecordParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;
use Sourcetoad\ShapeParser\Parsers\TupleParser;
use Sourcetoad\ShapeParser\Parsers\UnionParser;

final class Shape
{
    /**
     * @template T
     *
     * @param  list<ParserContract<T>>  $parsers
     * @return DiscriminatedUnionParser<T>
     */
    public static function discriminatedUnion(string $discriminator, array $parsers): DiscriminatedUnionParser
    {
        return new DiscriminatedUnionParser($discriminator, $parsers);
    }

    public static function boolean(): BooleanParser
    {
        return new BooleanParser;
    }

    public static function dateTime(): DateTimeParser
    {
        return new DateTimeParser;
    }

    /**
     * @template TEnum of \UnitEnum
     *
     * @param  class-string<TEnum>  $enumClass
     * @return EnumParser<TEnum>
     */
    public static function enum(string $enumClass): EnumParser
    {
        return new EnumParser($enumClass);
    }

    public static function float(): FloatParser
    {
        return new FloatParser;
    }

    public static function integer(): IntegerParser
    {
        return new IntegerParser;
    }

    /**
     * @template T of bool|int|string
     *
     * @param  T  $literal
     * @return LiteralParser<T>
     */
    public static function literal(bool|int|string $literal): LiteralParser
    {
        return new LiteralParser($literal);
    }

    /**
     * @template T
     *
     * @param  ParserContract<T>  $parser
     * @return ListParser<T>
     */
    public static function list(ParserContract $parser): ListParser
    {
        return new ListParser($parser);
    }

    public static function number(): NumberParser
    {
        return new NumberParser;
    }

    /**
     * @param  array<string, ParserContract<mixed>>  $shape
     * @return ObjectParser<array<array-key, mixed>>
     */
    public static function object(array $shape): ObjectParser
    {
        return new ObjectParser($shape);
    }

    /**
     * @template K of array-key
     * @template T
     *
     * @param  ParserContract<K>  $keyParser
     * @param  ParserContract<T>  $valueParser
     * @return RecordParser<K, T>
     */
    public static function record(ParserContract $keyParser, ParserContract $valueParser): RecordParser
    {
        return new RecordParser($keyParser, $valueParser);
    }

    public static function string(): StringParser
    {
        return new StringParser;
    }

    /**
     * @param  ParserContract<mixed>  ...$parsers
     * @return TupleParser<array<array-key, mixed>>
     */
    public static function tuple(ParserContract ...$parsers): TupleParser
    {
        return new TupleParser(...$parsers);
    }

    /**
     * @template T
     *
     * @param  ParserContract<T>  ...$parsers
     * @return UnionParser<T>
     */
    public static function union(ParserContract ...$parsers): UnionParser
    {
        return new UnionParser(...$parsers);
    }
}
