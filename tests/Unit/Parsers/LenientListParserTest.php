<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\LenientListParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(LenientListParser::class)]
class LenientListParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse(mixed $input, array $expected): void
    {
        // Arrange
        $lenientParser = Modifiers::lenient(new ListParser(new StringParser));

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
    public function test_parse_throws_when_input_is_invalid(mixed $input, string $expectedMessage): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        // Arrange
        $lenientParser = Modifiers::lenient(new ListParser(new StringParser));

        // Act
        $lenientParser->parse($input);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{input: mixed, expectedMessage: string}>
     */
    public static function invalidInputProvider(): array
    {
        return [
            'not an array' => [
                'input' => 'not an array',
                'expectedMessage' => 'Expected lenient<list<string>>, got string',
            ],
            'not a list' => [
                'input' => ['a' => 'foo', 'b' => 'bar'],
                'expectedMessage' => 'Expected lenient<list<string>>, got array with keys: a, b',
            ],
        ];
    }

    public function test_describe_returns_lenient_wrapped_description(): void
    {
        // Arrange
        $lenientParser = Modifiers::lenient(new ListParser(new StringParser));

        // Act
        $description = $lenientParser->describe();

        // Assert
        $this->assertSame('lenient<list<string>>', $description);
    }
}
