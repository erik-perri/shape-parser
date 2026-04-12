<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use InvalidArgumentException;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use stdClass;

/**
 * @template T
 * @extends BaseParser<T>
 */
final readonly class DiscriminatedUnionParser extends BaseParser
{
    /**
     * @var array<string|int, ParserContract<T>> $map
     */
    private array $map;

    /**
     * @param string $discriminator
     * @param list<ParserContract<T>> $parsers
     */
    public function __construct(
        private string $discriminator,
        array $parsers,
    ) {
        $map = [];

        foreach ($parsers as $i => $parser) {
            $objectParser = $this->unwrapToObject($parser);

            if ($objectParser === null) {
                throw new InvalidArgumentException(sprintf(
                    'Parser at index %d must be an ObjectParser (optionally wrapped in TransformParser), got %s',
                    $i,
                    get_debug_type($parser),
                ));
            }

            $fieldParser = $objectParser->shape[$discriminator] ?? null;

            if (!$fieldParser instanceof LiteralParser) {
                throw new InvalidArgumentException(sprintf(
                    'ObjectParser at index %d must have a LiteralParser for the "%s" field, got %s',
                    $i,
                    $discriminator,
                    $fieldParser === null ? 'missing field' : get_debug_type($fieldParser),
                ));
            }

            $tagValue = $fieldParser->literal;

            if (!is_string($tagValue) && !is_int($tagValue)) {
                throw new InvalidArgumentException(sprintf(
                    'Discriminator literal at index %d must be a string or int, got %s',
                    $i,
                    get_debug_type($tagValue),
                ));
            }

            if (array_key_exists($tagValue, $map)) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate discriminator value "%s" at index %d',
                    $tagValue,
                    $i,
                ));
            }

            $map[$tagValue] = $parser;
        }

        $this->map = $map;
    }

    /**
     * @param ParserContract<mixed> $parser
     * @return ObjectParser<array<mixed>>|null
     */
    private function unwrapToObject(ParserContract $parser): ?ObjectParser
    {
        return match (true) {
            $parser instanceof ObjectParser => $parser,
            $parser instanceof TransformParser => $this->unwrapToObject($parser->parser),
            default => null,
        };
    }

    public function describe(): string
    {
        return "discriminatedUnion<$this->discriminator>";
    }

    public function parse(mixed $data): mixed
    {
        if (!is_array($data) && !($data instanceof stdClass)) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        $data = (array) $data;

        if (!array_key_exists($this->discriminator, $data)) {
            throw new ParseException(sprintf(
                'Missing discriminator key "%s" in: %s',
                $this->discriminator,
                implode(', ', array_keys($data)),
            ));
        }

        $tagValue = $data[$this->discriminator];

        if (!is_string($tagValue) && !is_int($tagValue)) {
            throw new ParseException(sprintf(
                'Discriminator value for "%s" must be string or int, got %s',
                $this->discriminator,
                get_debug_type($tagValue),
            ));
        }

        if (!isset($this->map[$tagValue])) {
            $allowed = implode(', ', array_keys($this->map));

            throw new ParseException(sprintf('Unknown tag "%s". Expected one of: %s', $tagValue, $allowed));
        }

        return $this->map[$tagValue]->parse($data);
    }
}
