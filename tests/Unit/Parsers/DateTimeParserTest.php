<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\DateTimeParser;

#[CoversClass(DateTimeParser::class)]
class DateTimeParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse_returns_result(string $input, string $expected): void
    {
        // Arrange
        $parser = new DateTimeParser;

        // Act
        $result = $parser->parse($input);

        // Assert
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame($expected, $result->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @return array<string, array{input: string, expected: string}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'date only at midnight utc' => [
                'input' => '2026-04-12',
                'expected' => '2026-04-12T00:00:00.000000+00:00',
            ],
            'datetime with z suffix' => [
                'input' => '2026-04-12T10:30:00Z',
                'expected' => '2026-04-12T10:30:00.000000+00:00',
            ],
            'datetime with offset' => [
                'input' => '2026-04-12T10:30:00+02:00',
                'expected' => '2026-04-12T10:30:00.000000+02:00',
            ],
            'datetime with milliseconds' => [
                'input' => '2026-04-12T10:30:00.123Z',
                'expected' => '2026-04-12T10:30:00.123000+00:00',
            ],
            'datetime with microseconds' => [
                'input' => '2026-04-12T10:30:00.123456Z',
                'expected' => '2026-04-12T10:30:00.123456+00:00',
            ],
            'datetime without timezone as utc' => [
                'input' => '2026-04-12T10:30:00',
                'expected' => '2026-04-12T10:30:00.000000+00:00',
            ],
        ];
    }

    #[DataProvider('invalidCasesProvider')]
    public function test_parse_throws_when_invalid(mixed $input, string $expectedMessage): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        // Arrange
        $parser = new DateTimeParser;

        // Act
        $parser->parse($input);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{input: mixed, expectedMessage: string}>
     */
    public static function invalidCasesProvider(): array
    {
        return [
            'not a string' => [
                'input' => 123,
                'expectedMessage' => 'Expected datetime, got int',
            ],
            'slash separator' => [
                'input' => '2026/04/12',
                'expectedMessage' => 'Expected datetime, got "2026/04/12"',
            ],
            'space separator' => [
                'input' => '2026-04-12 10:30:00',
                'expectedMessage' => 'Expected datetime, got "2026-04-12 10:30:00"',
            ],
            'relative string' => [
                'input' => 'tomorrow',
                'expectedMessage' => 'Expected datetime, got "tomorrow"',
            ],
            'impossible date' => [
                'input' => '2026-02-30',
                'expectedMessage' => 'Expected datetime, got "2026-02-30"',
            ],
        ];
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
