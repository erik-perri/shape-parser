<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers\Traits;

use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\FallbackParser;

/**
 * @template T
 */
trait HasFallback
{
    /**
     * @param  T  $fallback
     * @return FallbackParser<T>
     */
    public function fallback(mixed $fallback): FallbackParser
    {
        return Modifiers::fallback($this, $fallback);
    }
}
