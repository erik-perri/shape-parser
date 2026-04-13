<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers\Traits;

use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\TransformParser;

/**
 * @template T
 */
trait HasTransformed
{
    /**
     * @template TNewOut
     *
     * @param  callable(T): TNewOut  $fn
     * @return TransformParser<T, TNewOut>
     */
    public function transform(callable $fn): TransformParser
    {
        return Modifiers::transform($this, $fn);
    }
}
