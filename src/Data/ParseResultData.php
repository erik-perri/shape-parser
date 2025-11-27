<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Data;

use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @template T
 */
final readonly class ParseResultData
{
    /**
     * @param T $data
     */
    public function __construct(
        public bool $success,
        public mixed $data,
        public ?ParseException $error,
    ) {
        //
    }
}
