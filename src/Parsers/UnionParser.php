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

/**
 * @template T
 *
 * @extends BaseParser<T>
 */
final readonly class UnionParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    /**
     * @var array<int, ParserContract<T>>
     */
    private array $parsers;

    /**
     * @param  ParserContract<T>  ...$parser
     */
    public function __construct(ParserContract ...$parser)
    {
        $this->parsers = array_values($parser);
    }

    public function describe(): string
    {
        return implode('|', array_map(fn (ParserContract $parser) => $parser->describe(), $this->parsers));
    }

    public function parse(mixed $data): mixed
    {
        foreach ($this->parsers as $parser) {
            $result = $parser->safeParse($data);

            if ($result->success) {
                /** @var T */
                return $result->data;
            }
        }

        throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
    }
}
