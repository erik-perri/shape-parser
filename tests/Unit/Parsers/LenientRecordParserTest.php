<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
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
        $parser = new RecordParser(new StringParser, new IntegerParser);
        $lenientParser = $parser->lenient();

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

        // Arrange
        $parser = new RecordParser(new StringParser, new IntegerParser);
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->parse('not an array');

        // Assert
        // No assertions, only expectations.
    }

    public function test_describe_returns_lenient_wrapped_description(): void
    {
        // Arrange
        $parser = new RecordParser(new StringParser, new IntegerParser);
        $lenientParser = $parser->lenient();

        // Act
        $description = $lenientParser->describe();

        // Assert
        $this->assertSame('lenient<record<string, int>>', $description);
    }

    public function test_double_lenient_throws(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call lenient() on an already lenient parser.');

        // Arrange
        $parser = new RecordParser(new StringParser, new IntegerParser);
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->lenient();

        // Assert
        // No assertions, only expectations.
    }
}
