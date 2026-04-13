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
 * @template T of mixed
 *
 * @extends BaseParser<list<T>>
 */
final readonly class ListParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    /** @use HasFallback<list<T>> */
    use HasFallback;

    /** @use HasLenient<list<T>> */
    use HasLenient;

    /** @use HasNullable<list<T>> */
    use HasNullable;

    /** @use HasOptional<list<T>> */
    use HasOptional;

    /** @use HasTransformed<list<T>> */
    use HasTransformed;

    /**
     * @return ParserContract<T>
     */
    public function innerParser(): ParserContract
    {
        return $this->parser;
    }

    /**
     * @param  ParserContract<T>  $parser
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
     * @return list<T>
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

        $result = [];
        $issues = [];

        foreach ($data as $index => $value) {
            try {
                $result[$index] = $this->parser->parse($value);
            } catch (ParseException $e) {
                foreach ($e->issues as $issue) {
                    $issues[] = $issue->withPrefix($index);
                }
            }
        }

        if ($issues !== []) {
            throw ParseException::fromIssues($issues);
        }

        /** @var list<T> */
        return $result;
    }
}
