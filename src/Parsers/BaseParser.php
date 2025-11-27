<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use Sourcetoad\ShapeParser\Data\ParseResultData;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;

/**
 * @template T
 * @implements ParserContract<T>
 */
abstract readonly class BaseParser implements ParserContract
{
    abstract public function parse(mixed $data): mixed;

    public function safeParse(mixed $data): ParseResultData
    {
        try {
            return new ParseResultData(true, $this->parse($data), null);
        } catch (ParseException $e) {
            return new ParseResultData(false, null, $e);
        }
    }
}
