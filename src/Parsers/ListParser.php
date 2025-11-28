<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use stdClass;

/**
 * @template T of mixed
 * @extends BaseParser<list<T>>
 */
final readonly class ListParser extends BaseParser
{
    /**
     * @param ParserContract<T> $parser
     */
    public function __construct(
        private ParserContract $parser,
    ) {
        //
    }

    public function describe(): string
    {
        return sprintf('list<%s>', $this->parser->describe());
    }

    /**
     * @param mixed $data
     * @return list<T>
     * @throws ParseException
     */
    public function parse(mixed $data): array
    {
        if (!is_array($data) && !($data instanceof stdClass)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        $data = (array) $data;

        if (!array_is_list($data)) {
            throw new ParseException(sprintf(
                'Expected %s, got array with keys: %s',
                $this->describe(),
                implode(', ', array_keys($data)),
            ));
        }

        $result = [];
        $errors = [];

        foreach ($data as $index => $value) {
            try {
                $result[$index] = $this->parser->parse($value);
            } catch (ParseException $e) {
                $errors[$index] = $e;
            }
        }

        if (!empty($errors)) {
            // TODO Better error reporting
            throw new ParseException('Failed to parse list');
        }

        // @phpstan-ignore return.type
        return $result;
    }
}
