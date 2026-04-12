<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\DateTimeParser;

#[CoversClass(DateTimeParser::class)]
class DateTimeParserTest extends TestCase
{
    public function test_parse_returns_date_only_at_midnight_utc(): void
    {
        // Arrange
        $parser = new DateTimeParser;

        // Act
        $result = $parser->parse('2026-04-12');

        // Assert
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('2026-04-12T00:00:00.000+00:00', $result->format(DateTimeInterface::RFC3339_EXTENDED));
    }

    public function test_parse_returns_datetime_with_z_suffix(): void
    {
        // Arrange
        $parser = new DateTimeParser;

        // Act
        $result = $parser->parse('2026-04-12T10:30:00Z');

        // Assert
        $this->assertSame('2026-04-12T10:30:00.000+00:00', $result->format(DateTimeInterface::RFC3339_EXTENDED));
    }

    public function test_parse_returns_datetime_with_offset(): void
    {
        // Arrange
        $parser = new DateTimeParser;

        // Act
        $result = $parser->parse('2026-04-12T10:30:00+02:00');

        // Assert
        $this->assertSame('2026-04-12T10:30:00.000+02:00', $result->format(DateTimeInterface::RFC3339_EXTENDED));
    }

    public function test_parse_returns_datetime_with_milliseconds(): void
    {
        // Arrange
        $parser = new DateTimeParser;

        // Act
        $result = $parser->parse('2026-04-12T10:30:00.123Z');

        // Assert
        $this->assertSame('2026-04-12T10:30:00.123+00:00', $result->format(DateTimeInterface::RFC3339_EXTENDED));
    }

    public function test_parse_returns_datetime_with_microseconds(): void
    {
        // Arrange
        $parser = new DateTimeParser;

        // Act
        $result = $parser->parse('2026-04-12T10:30:00.123456Z');

        // Assert
        $this->assertSame('123456', $result->format('u'));
        $this->assertSame('+00:00', $result->format('P'));
    }

    public function test_parse_returns_datetime_without_timezone_as_utc(): void
    {
        // Arrange
        $parser = new DateTimeParser;

        // Act
        $result = $parser->parse('2026-04-12T10:30:00');

        // Assert
        $this->assertSame('2026-04-12T10:30:00.000+00:00', $result->format(DateTimeInterface::RFC3339_EXTENDED));
    }

    public function test_parse_throws_when_not_string(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected datetime, got int');

        // Arrange
        $parser = new DateTimeParser;

        // Act
        $parser->parse(123);

        // Assert
        // No assertions, only expectations.
    }

    public function test_parse_throws_on_slash_separator(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected datetime, got "2026/04/12"');

        // Arrange
        $parser = new DateTimeParser;

        // Act
        $parser->parse('2026/04/12');

        // Assert
        // No assertions, only expectations.
    }

    public function test_parse_throws_on_space_separator(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected datetime, got "2026-04-12 10:30:00"');

        // Arrange
        $parser = new DateTimeParser;

        // Act
        $parser->parse('2026-04-12 10:30:00');

        // Assert
        // No assertions, only expectations.
    }

    public function test_parse_throws_on_relative_string(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected datetime, got "tomorrow"');

        // Arrange
        $parser = new DateTimeParser;

        // Act
        $parser->parse('tomorrow');

        // Assert
        // No assertions, only expectations.
    }

    public function test_parse_throws_on_impossible_date(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected datetime, got "2026-02-30"');

        // Arrange
        $parser = new DateTimeParser;

        // Act
        $parser->parse('2026-02-30');

        // Assert
        // No assertions, only expectations.
    }

    public function test_describe(): void
    {
        // Arrange
        $parser = new DateTimeParser;

        // Act
        $description = $parser->describe();

        // Assert
        $this->assertSame('datetime', $description);
    }
}
