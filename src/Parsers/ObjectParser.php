<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use ParseError;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;

/**
 * @template Tk of array-key
 * @template Tv
 * @extends BaseParser<array<Tk, Tv>>
 */
final readonly class ObjectParser extends BaseParser
{
    /**
     * @param array<Tk, ParserContract<Tv>> $shape
     */
    public function __construct(
        private array $shape,
    ) {
        //
    }

    /**
     * @param mixed $data
     * @return array<Tk, Tv>
     * @throws ParseException
     */
    public function parse(mixed $data): array
    {
        if (!is_array($data) && !($data instanceof \stdClass)) {
            throw new ParseException("Expected object or array, got " . get_debug_type($data));
        }

        $data = (array) $data;
        $result = [];
        $errors = [];

        foreach ($this->shape as $key => $parser) {
            if (!array_key_exists($key, $data)) {
                $errors[$key] = new ParseError("Missing required field: $key");
                continue;
            }

            try {
                $result[$key] = $parser->parse($data[$key]);
            } catch (ParseException $e) {
                $errors[$key] = $e;
            }
        }

        if (!empty($errors)) {
            // TODO Better error reporting
            throw new ParseException('Failed to parse object.');
        }

        return $result;
    }
}
