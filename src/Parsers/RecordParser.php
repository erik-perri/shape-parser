<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use stdClass;

/**
 * @template K of array-key
 * @template T of mixed
 * @extends BaseParser<array<K, T>>
 */
final readonly class RecordParser extends BaseParser
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

    /**
     * @param mixed $data
     * @return array<K, T>
     * @throws ParseException
     */
    public function parse(mixed $data): array
    {
        if (!is_array($data) && !($data instanceof stdClass)) {
            throw new ParseException("Expected record, got " . get_debug_type($data));
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

        if (!empty($errors)) {
            // TODO Better error reporting
            throw new ParseException('Failed to parse record');
        }

        return $result;
    }
}
