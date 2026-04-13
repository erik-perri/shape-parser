<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Data\ParseResultData;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;

/**
 * @template T
 *
 * @extends BaseParser<T>
 */
final readonly class FallbackParser extends BaseParser implements CanBeOptional, CanBeTransformed
{
    /**
     * @param  ParserContract<T>  $parser
     * @param  T  $fallback
     */
    public function __construct(
        private ParserContract $parser,
        private mixed $fallback,
    ) {
        //
    }

    public function describe(): string
    {
        return sprintf('fallback<%s, %s>', $this->parser->describe(), $this->describeFallback());
    }

    /**
     * @return T
     */
    public function parse(mixed $data): mixed
    {
        try {
            return $this->parser->parse($data);
        } catch (ParseException) {
            return $this->fallback;
        }
    }

    public function safeParse(mixed $data): ParseResultData
    {
        return new ParseResultData(true, $this->parse($data), null);
    }

    public function isOptional(): bool
    {
        return $this->parser instanceof BaseParser && $this->parser->isOptional();
    }

    private function describeFallback(): string
    {
        return match (true) {
            is_string($this->fallback) => sprintf("'%s'", $this->fallback),
            is_bool($this->fallback) => $this->fallback ? 'true' : 'false',
            is_int($this->fallback), is_float($this->fallback) => (string) $this->fallback,
            is_null($this->fallback) => 'null',
            default => get_debug_type($this->fallback),
        };
    }
}
