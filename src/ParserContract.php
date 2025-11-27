<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser;

use Sourcetoad\ShapeParser\Data\ParseResultData;
use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @template-covariant T
 */
interface ParserContract
{
    /**
     * @return T
     * @throws ParseException
     */
    public function parse(mixed $data): mixed;

    /**
     * @return ParseResultData<T>|ParseResultData<null>
     */
    public function safeParse(mixed $data): ParseResultData;
}
