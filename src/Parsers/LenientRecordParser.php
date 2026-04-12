<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use LogicException;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use stdClass;

/**
 * @template K of array-key
 * @template T of mixed
 * @extends BaseParser<array<K, T>>
 */
final readonly class LenientRecordParser extends BaseParser
{
    /**
     * @param ParserContract<K> $keyParser
     * @param ParserContract<T> $valueParser
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
     * @param mixed $data
     * @return array<K, T>
     * @throws ParseException
     */
    public function parse(mixed $data): array
    {
        if (!is_array($data) && !($data instanceof stdClass)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        $data = (array) $data;

        /**
         * @var array<K, T> $result
         */
        $result = [];

        foreach ($data as $key => $value) {
            $parsedKey = $this->keyParser->safeParse($key);

            if (!$parsedKey->success) {
                continue;
            }

            $parsedValue = $this->valueParser->safeParse($value);

            if (!$parsedValue->success) {
                continue;
            }

            /** @var K $key */
            $key = $parsedKey->data;
            $result[$key] = $parsedValue->data;
        }

        /** @var array<K, T> */
        return $result;
    }

    public function lenient(): never
    {
        throw new LogicException('Cannot call lenient() on an already lenient parser.');
    }
}
