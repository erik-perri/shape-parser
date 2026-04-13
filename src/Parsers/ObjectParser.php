<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use ParseError;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeNullable;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use stdClass;

/**
 * @template T of array<mixed>
 *
 * @extends BaseParser<T>
 */
final readonly class ObjectParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    /**
     * @param  array<string, ParserContract<mixed>>  $shape
     */
    public function __construct(
        public array $shape,
    ) {
        //
    }

    public function describe(): string
    {
        // TODO Expand?
        return 'object';
    }

    /**
     * @return T
     *
     * @throws ParseException
     */
    public function parse(mixed $data): array
    {
        if (! is_array($data) && ! ($data instanceof stdClass)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        $data = (array) $data;
        $result = [];
        $errors = [];

        foreach ($this->shape as $key => $parser) {
            if (! array_key_exists($key, $data)) {
                if ($parser instanceof BaseParser && $parser->isOptional()) {
                    continue;
                }

                $errors[$key] = new ParseError("Missing required field: $key");

                continue;
            }

            try {
                $result[$key] = $parser->parse($data[$key]);
            } catch (ParseException $e) {
                $errors[$key] = $e;
            }
        }

        if (! empty($errors)) {
            // TODO Better error reporting
            throw new ParseException('Failed to parse object');
        }

        /** @var T */
        return $result;
    }
}
