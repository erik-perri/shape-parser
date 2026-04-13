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
 * @template T of array<array-key, mixed>
 *
 * @extends BaseParser<T>
 */
final readonly class TupleParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    /** @use HasFallback<T> */
    use HasFallback;

    /** @use HasLenient<T> */
    use HasLenient;

    /** @use HasNullable<T> */
    use HasNullable;

    /** @use HasOptional<T> */
    use HasOptional;

    /** @use HasTransformed<T> */
    use HasTransformed;

    /** @var list<ParserContract<mixed>> */
    private array $parsers;

    /**
     * @param  ParserContract<mixed>  ...$parsers
     */
    public function __construct(ParserContract ...$parsers)
    {
        $this->parsers = array_values($parsers);
    }

    public function describe(): string
    {
        return sprintf(
            'tuple<%s>',
            implode(', ', array_map(static fn (ParserContract $parser): string => $parser->describe(), $this->parsers)),
        );
    }

    /**
     * @return T
     *
     * @throws ParseException
     */
    public function parse(mixed $data): array
    {
        if (! is_array($data) && ! ($data instanceof stdClass)) {
            throw ParseException::fromMessage(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        $data = (array) $data;

        if (! array_is_list($data)) {
            throw ParseException::fromMessage(sprintf(
                'Expected %s, got array with keys: %s',
                $this->describe(),
                implode(', ', array_keys($data)),
            ));
        }

        $expected = count($this->parsers);
        $actual = count($data);

        if ($expected !== $actual) {
            throw ParseException::fromMessage(sprintf(
                'Expected %s of length %d, got %d',
                $this->describe(),
                $expected,
                $actual,
            ));
        }

        $result = [];
        $issues = [];

        foreach ($this->parsers as $index => $parser) {
            try {
                $result[$index] = $parser->parse($data[$index]);
            } catch (ParseException $e) {
                foreach ($e->issues as $issue) {
                    $issues[] = $issue->withPrefix($index);
                }
            }
        }

        if ($issues !== []) {
            throw ParseException::fromIssues($issues);
        }

        /** @var T */
        return $result;
    }
}
