<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Data\ParseIssue;
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
 * @template T of array<mixed>
 *
 * @extends BaseParser<T>
 */
final readonly class ObjectParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
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
            throw ParseException::fromMessage(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        $data = (array) $data;
        $result = [];
        $issues = [];

        foreach ($this->shape as $key => $parser) {
            if (! array_key_exists($key, $data)) {
                if ($parser instanceof BaseParser && $parser->isOptional()) {
                    continue;
                }

                $issues[] = new ParseIssue([$key], 'Missing required field');

                continue;
            }

            try {
                $result[$key] = $parser->parse($data[$key]);
            } catch (ParseException $e) {
                foreach ($e->issues as $issue) {
                    $issues[] = $issue->withPrefix($key);
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
