<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use BackedEnum;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use UnitEnum;

/**
 * @template TEnum of UnitEnum
 *
 * @extends BaseParser<TEnum>
 */
final readonly class EnumParser extends BaseParser
{
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
            throw new ParseException(sprintf('Expected %s, got %s', $this->describe(), get_debug_type($data)));
        }

        if (is_subclass_of($this->enumClass, BackedEnum::class)) {
            $value = $this->enumClass::tryFrom($data);

            if ($value === null) {
                throw new ParseException(sprintf('Expected %s, got "%s"', $this->describe(), $data));
            }

            return $value;
        }

        foreach ($this->enumClass::cases() as $case) {
            if ($case->name === (string) $data) {
                return $case;
            }
        }

        throw new ParseException(sprintf('Expected %s, got "%s"', $this->describe(), $data));
    }
}
