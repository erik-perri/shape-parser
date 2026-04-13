<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\LenientRecordParser;
use Sourcetoad\ShapeParser\Parsers\RecordParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(LenientRecordParser::class)]
class LenientRecordParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse(mixed $input, array $expected): void
    {
        // Arrange
        $lenientParser = Modifiers::lenient(new RecordParser(new StringParser, new IntegerParser));

        // Act
        $result = $lenientParser->parse($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{input: mixed, expected: array<string, int>}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'all valid' => [
                'input' => json_decode('{"a": 1, "b": 2, "c": 3}'),
                'expected' => ['a' => 1, 'b' => 2, 'c' => 3],
            ],
            'drops invalid entries' => [
                'input' => ['a' => 1, 'b' => 'not int', 'c' => 3],
                'expected' => ['a' => 1, 'c' => 3],
            ],
            'all entries fail' => [
                'input' => ['a' => 'x', 'b' => 'y'],
                'expected' => [],
            ],
        ];
    }

    public function test_parse_throws_when_input_is_not_array(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected lenient<record<string, int>>, got string');

        // Arrange
        $lenientParser = Modifiers::lenient(new RecordParser(new StringParser, new IntegerParser));

        // Act
        $lenientParser->parse('not an array');

        // Assert
        // No assertions, only expectations.
    }

    public function test_describe_returns_lenient_wrapped_description(): void
    {
        // Arrange
        $lenientParser = Modifiers::lenient(new RecordParser(new StringParser, new IntegerParser));

        // Act
        $description = $lenientParser->describe();

        // Assert
        $this->assertSame('lenient<record<string, int>>', $description);
    }
}
