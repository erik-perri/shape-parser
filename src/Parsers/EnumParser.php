<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use BackedEnum;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeFallback;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeLenient;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeNullable;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeOptional;
use Sourcetoad\ShapeParser\Parsers\Contracts\CanBeTransformed;
use Sourcetoad\ShapeParser\Parsers\Traits\HasFallback;
use Sourcetoad\ShapeParser\Parsers\Traits\HasLenient;
use Sourcetoad\ShapeParser\Parsers\Traits\HasNullable;
use Sourcetoad\ShapeParser\Parsers\Traits\HasOptional;
use Sourcetoad\ShapeParser\Parsers\Traits\HasTransformed;
use UnitEnum;

/**
 * @template TEnum of UnitEnum
 *
 * @extends BaseParser<TEnum>
 */
final readonly class EnumParser extends BaseParser implements CanBeFallback, CanBeLenient, CanBeNullable, CanBeOptional, CanBeTransformed
{
    /** @use HasFallback<TEnum> */
    use HasFallback;

    /** @use HasLenient<TEnum> */
    use HasLenient;

    /** @use HasNullable<TEnum> */
    use HasNullable;

    /** @use HasOptional<TEnum> */
    use HasOptional;

    /** @use HasTransformed<TEnum> */
    use HasTransformed;

    /**
     * @param  class-string<TEnum>  $enumClass
     */
    public function __construct(
        private string $enumClass
    ) {
        //
    }

    public function describe(): string
    {
        return sprintf('enum<%s>', $this->enumClass);
    }

    /**
     * @return TEnum
     */
    public function parse(mixed $data): UnitEnum
    {
        if (! is_string($data) && ! is_int($data)) {
            throw ParseException::fromMessage(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        if (is_subclass_of($this->enumClass, BackedEnum::class)) {
            $value = $this->enumClass::tryFrom($data);

            if ($value === null) {
                throw ParseException::fromMessage(sprintf('Expected %s, got "%s"', $this->describe(), $data));
            }

            return $value;
        }

        foreach ($this->enumClass::cases() as $case) {
            if ($case->name === (string) $data) {
                return $case;
            }
        }

        throw ParseException::fromMessage(sprintf('Expected %s, got "%s"', $this->describe(), $data));
    }
}
