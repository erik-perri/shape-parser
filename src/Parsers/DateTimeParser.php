<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Parsers;

use DateTimeImmutable;
use DateTimeZone;
use Sourcetoad\ShapeParser\Exceptions\ParseException;

/**
 * @extends BaseParser<DateTimeImmutable>
 */
final readonly class DateTimeParser extends BaseParser
{
    private const FORMATS = [
        '!Y-m-d\TH:i:s.up',
        '!Y-m-d\TH:i:s.vp',
        '!Y-m-d\TH:i:sp',
        '!Y-m-d\TH:i:s.u',
        '!Y-m-d\TH:i:s.v',
        '!Y-m-d\TH:i:s',
        '!Y-m-d',
    ];

    public function describe(): string
    {
        return 'datetime';
    }

    public function parse(mixed $data): DateTimeImmutable
    {
        if (! is_string($data)) {
            throw new ParseException(sprintf('Expected datetime, got %s', get_debug_type($data)));
        }

        $utc = new DateTimeZone('UTC');

        foreach (self::FORMATS as $format) {
            $result = DateTimeImmutable::createFromFormat($format, $data, $utc);
            $errors = DateTimeImmutable::getLastErrors();

            $hasErrors = is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

            if ($result !== false && ! $hasErrors) {
                return $result;
            }
        }

        throw new ParseException(sprintf('Expected datetime, got "%s"', $data));
    }
}
