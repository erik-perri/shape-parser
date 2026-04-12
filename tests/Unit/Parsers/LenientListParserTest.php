<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\LenientListParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(LenientListParser::class)]
class LenientListParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function testParse(mixed $input, array $expected): void
    {
        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{input: mixed, expected: array<mixed>}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'all valid' => [
                'input' => json_decode('["foo", "bar", "baz"]'),
                'expected' => ['foo', 'bar', 'baz'],
            ],
            'drops invalid items' => [
                'input' => ['a', 123, 'b', true, 'c'],
                'expected' => ['a', 'b', 'c'],
            ],
            'all items fail' => [
                'input' => [1, 2, 3],
                'expected' => [],
            ],
        ];
    }

    #[DataProvider('invalidInputProvider')]
    public function testParseThrowsWhenInputIsInvalid(mixed $input): void
    {
        // Expectations
        $this->expectException(ParseException::class);

        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->parse($input);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{input: mixed}>
     */
    public static function invalidInputProvider(): array
    {
        return [
            'not an array' => [
                'input' => 'not an array',
            ],
            'not a list' => [
                'input' => ['a' => 'foo', 'b' => 'bar'],
            ],
        ];
    }

    public function testDescribeReturnsLenientWrappedDescription(): void
    {
        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $description = $lenientParser->describe();

        // Assert
        $this->assertSame('lenient<list<string>>', $description);
    }

    public function testDoubleLenientThrows(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call lenient() on an already lenient parser.');

        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->lenient();

        // Assert
        // No assertions, only expectations.
    }
}
