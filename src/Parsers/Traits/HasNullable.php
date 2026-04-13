<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers\Traits;

use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\NullableParser;

/**
 * @template T
 */
trait HasNullable
{
    /**
     * @return NullableParser<T>
     */
    public function nullable(): NullableParser
    {
        return Modifiers::nullable($this);
    }
}
