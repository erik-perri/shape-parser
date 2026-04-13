<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use Sourcetoad\ShapeParser\Parsers\Traits\HasOptional;
use Sourcetoad\ShapeParser\Parsers\Traits\HasTransformed;

/**
 * @template T
 *
 * @extends BaseParser<T|null>
 */
final readonly class LenientParser extends BaseParser implements CanBeOptional, CanBeTransformed
{
    /** @use HasOptional<T|null> */
    use HasOptional;

    /** @use HasTransformed<T|null> */
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
        return sprintf('lenient<%s>', $this->parser->describe());
    }

    public function parse(mixed $data): mixed
    {
        $result = $this->parser->safeParse($data);

        return $result->success ? $result->data : null;
    }

    public function isOptional(): bool
    {
        return $this->parser instanceof BaseParser && $this->parser->isOptional();
    }
}
