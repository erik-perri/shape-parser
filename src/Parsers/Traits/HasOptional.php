<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers\Traits;

use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\OptionalParser;

/**
 * @template T
 */
trait HasOptional
{
    /**
     * @return OptionalParser<T>
     */
    public function optional(): OptionalParser
    {
        return Modifiers::optional($this);
    }
}
