<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser;

use Sourcetoad\ShapeParser\Parsers\BaseParser;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeNullable;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use Sourcetoad\ShapeParser\Parsers\FallbackParser;
use Sourcetoad\ShapeParser\Parsers\LenientListParser;
use Sourcetoad\ShapeParser\Parsers\LenientParser;
use Sourcetoad\ShapeParser\Parsers\LenientRecordParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\NullableParser;
use Sourcetoad\ShapeParser\Parsers\OptionalParser;
use Sourcetoad\ShapeParser\Parsers\RecordParser;
use Sourcetoad\ShapeParser\Parsers\TransformParser;

final class Modifiers
{
    /**
     * @template T
     *
     * @param  BaseParser<T>&CanBeNullable  $parser
     * @return NullableParser<T>
     */
    public static function nullable(BaseParser $parser): NullableParser
    {
        /** @var NullableParser<T> */
        return new NullableParser($parser);
    }

    /**
     * @template T
     *
     * @param  BaseParser<T>&CanBeOptional  $parser
     * @return OptionalParser<T>
     */
    public static function optional(BaseParser $parser): OptionalParser
    {
        /** @var OptionalParser<T> */
        return new OptionalParser($parser);
    }

    /**
     * The actual return type is narrowed by LenientReturnTypeExtension:
     * - ListParser<T> -> LenientListParser<T>
     * - RecordParser<K, T> -> LenientRecordParser<K, T>
     * - otherwise LenientParser<T>
     *
     * @template T
     *
     * @param  BaseParser<T>&CanBeLenient  $parser
     * @return BaseParser<mixed>
     */
    public static function lenient(BaseParser $parser): BaseParser
    {
        if ($parser instanceof ListParser) {
            return self::lenientList($parser);
        }

        if ($parser instanceof RecordParser) {
            return self::lenientRecord($parser);
        }

        return new LenientParser($parser);
    }

    /**
     * @template T
     *
     * @param  ListParser<T>  $parser
     * @return LenientListParser<T>
     */
    private static function lenientList(ListParser $parser): LenientListParser
    {
        return new LenientListParser($parser->innerParser());
    }

    /**
     * @template K of array-key
     * @template T
     *
     * @param  RecordParser<K, T>  $parser
     * @return LenientRecordParser<K, T>
     */
    private static function lenientRecord(RecordParser $parser): LenientRecordParser
    {
        return new LenientRecordParser($parser->innerKeyParser(), $parser->innerValueParser());
    }

    /**
     * @template TIn
     * @template TOut
     *
     * @param  BaseParser<TIn>&CanBeTransformed  $parser
     * @param  callable(TIn): TOut  $fn
     * @return TransformParser<TIn, TOut>
     */
    public static function transform(BaseParser $parser, callable $fn): TransformParser
    {
        return new TransformParser($parser, $fn(...));
    }

    /**
     * @template T
     *
     * @param  BaseParser<T>&CanBeFallback  $parser
     * @param  T  $fallback
     * @return FallbackParser<T>
     */
    public static function fallback(BaseParser $parser, mixed $fallback): FallbackParser
    {
        /** @var FallbackParser<T> */
        return new FallbackParser($parser, $fallback);
    }
}
