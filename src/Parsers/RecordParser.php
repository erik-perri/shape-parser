<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeNullable;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use Sourcetoad\ShapeParser\Parsers\Traits\HasFallback;
use Sourcetoad\ShapeParser\Parsers\Traits\HasLenient;
use Sourcetoad\ShapeParser\Parsers\Traits\HasNullable;
use Sourcetoad\ShapeParser\Parsers\Traits\HasOptional;
use Sourcetoad\ShapeParser\Parsers\Traits\HasTransformed;
use stdClass;

/**
 * @template K of array-key
 * @template T of mixed
 *
 * @extends BaseParser<array<K, T>>
 */
final readonly class RecordParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    /** @use HasFallback<array<K, T>> */
    use HasFallback;

    /** @use HasLenient<array<K, T>> */
    use HasLenient;

    /** @use HasNullable<array<K, T>> */
    use HasNullable;

    /** @use HasOptional<array<K, T>> */
    use HasOptional;

    /** @use HasTransformed<array<K, T>> */
    use HasTransformed;

    /**
     * @return ParserContract<K>
     */
    public function innerKeyParser(): ParserContract
    {
        return $this->keyParser;
    }

    /**
     * @return ParserContract<T>
     */
    public function innerValueParser(): ParserContract
    {
        return $this->valueParser;
    }

    /**
     * @param  ParserContract<K>  $keyParser
     * @param  ParserContract<T>  $valueParser
     */
    public function __construct(
        private ParserContract $keyParser,
        private ParserContract $valueParser,
    ) {
        //
    }

    public function describe(): string
    {
        return sprintf('record<%s, %s>', $this->keyParser->describe(), $this->valueParser->describe());
    }

    /**
     * @return array<K, T>
     *
     * @throws ParseException
     */
    public function parse(mixed $data): array
    {
        if (! is_array($data) && ! ($data instanceof stdClass)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        $data = (array) $data;

        /**
         * @var array<K, T> $result
         */
        $result = [];
        $errors = [];

        foreach ($data as $key => $value) {
            try {
                $key = $this->keyParser->parse($key);
                $value = $this->valueParser->parse($value);

                $result[$key] = $value;
            } catch (ParseException $e) {
                $errors[$key] = $e;
            }
        }

        if (! empty($errors)) {
            // TODO Better error reporting
            throw new ParseException('Failed to parse record');
        }

        return $result;
    }
}
