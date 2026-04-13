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
 * @template T of mixed
 *
 * @extends BaseParser<list<T>>
 */
final readonly class LenientListParser extends BaseParser implements CanBeOptional, CanBeTransformed
{
    /** @use HasOptional<list<T>> */
    use HasOptional;

    /** @use HasTransformed<list<T>> */
    use HasTransformed;

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
        return sprintf('lenient<list<%s>>', $this->parser->describe());
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

        foreach ($data as $value) {
            $parsed = $this->parser->safeParse($value);

            if ($parsed->success) {
                $result[] = $parsed->data;
            }
            // TODO: expose ignored ParseIssues via a user-supplied hook (design TBD)
        }

        /** @var list<T> */
        return $result;
    }
}
