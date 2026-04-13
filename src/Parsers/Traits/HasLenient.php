<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers\Traits;

use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\BaseParser;

/**
 * @template T
 */
trait HasLenient
{
    /**
     * The actual return type is narrowed by LenientMethodReturnTypeExtension:
     * - ListParser<T> -> LenientListParser<T>
     * - RecordParser<K, T> -> LenientRecordParser<K, T>
     * - otherwise LenientParser<T>
     *
     * @return BaseParser<mixed>
     */
    public function lenient(): BaseParser
    {
        return Modifiers::lenient($this);
    }
}
