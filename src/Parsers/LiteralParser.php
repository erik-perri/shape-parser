<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @template T of bool|int|string
 *
 * @extends BaseParser<T>
 */
final readonly class LiteralParser extends BaseParser
{
    /**
     * @param  T  $literal
     */
    public function __construct(public bool|int|string $literal)
    {
        //
    }

    public function describe(): string
    {
        $value = $this->literal;

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        if (is_int($value)) {
            $value = (string) $value;
        }

        return sprintf('literal(%s)', $value);
    }

    /**
     * @return T
     */
    public function parse(mixed $data): mixed
    {
        if ($data !== $this->literal) {
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        return $data;
    }
}
