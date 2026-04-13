<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use Sourcetoad\ShapeParser\Parsers\Traits\HasOptional;
use Sourcetoad\ShapeParser\Parsers\Traits\HasTransformed;
use stdClass;

/**
 * @template K of array-key
 * @template T of mixed
 *
 * @extends BaseParser<array<K, T>>
 */
final readonly class LenientRecordParser extends BaseParser implements CanBeOptional, CanBeTransformed
{
    /** @use HasOptional<array<K, T>> */
    use HasOptional;

    /** @use HasTransformed<array<K, T>> */
    use HasTransformed;

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
        return sprintf('lenient<record<%s, %s>>', $this->keyParser->describe(), $this->valueParser->describe());
    }

    /**
     * @return array<K, T>
     *
     * @throws ParseException
     */
    public function parse(mixed $data): array
    {
        if (! is_array($data) && ! ($data instanceof stdClass)) {
            throw ParseException::fromMessage(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        $data = (array) $data;

        /**
         * @var array<K, T> $result
         */
        $result = [];

        foreach ($data as $key => $value) {
            $parsedKey = $this->keyParser->safeParse($key);

            if (! $parsedKey->success) {
                // TODO: expose ignored ParseIssues via a user-supplied hook (design TBD)
                continue;
            }

            $parsedValue = $this->valueParser->safeParse($value);

            if (! $parsedValue->success) {
                // TODO: expose ignored ParseIssues via a user-supplied hook (design TBD)
                continue;
            }

            /** @var K $key */
            $key = $parsedKey->data;
            $result[$key] = $parsedValue->data;
        }

        /** @var array<K, T> */
        return $result;
    }
}
