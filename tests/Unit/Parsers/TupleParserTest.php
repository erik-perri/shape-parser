<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Data\ParseIssue;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\FloatParser;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;
use Sourcetoad\ShapeParser\Parsers\TupleParser;

#[CoversClass(TupleParser::class)]
class TupleParserTest extends TestCase
{
    public function test_parse_returns_result(): void
    {
        // Arrange
        $parser = new TupleParser(new StringParser, new IntegerParser);
        $data = json_decode('["foo", 42]');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['foo', 42], $result);
    }

    public function test_describe_returns_expected_format(): void
    {
        // Arrange
        $parser = new TupleParser(new StringParser, new IntegerParser, new FloatParser);

        // Act
        $description = $parser->describe();

        // Assert
        $this->assertSame('tuple<string, int, float>', $description);
    }

    #[DataProvider('parseCasesProvider')]
    public function test_parse(TupleParser $parser, mixed $data, array $expected): void
    {
        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{parser: TupleParser, data: mixed, expected: array<array-key, mixed>}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'two element tuple' => [
                'parser' => new TupleParser(new StringParser, new IntegerParser),
                'data' => json_decode('["foo", 42]'),
                'expected' => ['foo', 42],
            ],
            'three element tuple of floats' => [
                'parser' => new TupleParser(new FloatParser, new FloatParser, new FloatParser),
                'data' => json_decode('[1.5, 2.5, 3.5]'),
                'expected' => [1.5, 2.5, 3.5],
            ],
            'tuple containing nested list' => [
                'parser' => new TupleParser(new StringParser, new ListParser(new IntegerParser)),
                'data' => json_decode('["items", [1, 2, 3]]'),
                'expected' => ['items', [1, 2, 3]],
            ],
            'stdClass input treated as list' => [
                'parser' => new TupleParser(new StringParser, new IntegerParser),
                'data' => (object) ['foo', 42],
                'expected' => ['foo', 42],
            ],
        ];
    }

    #[DataProvider('invalidCasesProvider')]
    public function test_parse_throws_when_invalid(TupleParser $parser, mixed $data, string $expectedMessage): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{parser: TupleParser, data: mixed, expectedMessage: list<ParseIssue>}>
     */
    public static function invalidCasesProvider(): array
    {
        $stringInt = new TupleParser(new StringParser, new IntegerParser);

        return [
            'not an array' => [
                'parser' => $stringInt,
                'data' => 'foo',
                'expectedMessage' => 'Expected tuple<string, int>, got string',
            ],
            'associative array not a list' => [
                'parser' => $stringInt,
                'data' => ['a' => 'foo', 'b' => 42],
                'expectedMessage' => 'Expected tuple<string, int>, got array with keys: a, b',
            ],
            'too few elements' => [
                'parser' => $stringInt,
                'data' => json_decode('["foo"]'),
                'expectedMessage' => 'Expected tuple<string, int> of length 2, got 1',
            ],
            'too many elements' => [
                'parser' => $stringInt,
                'data' => json_decode('["foo", 42, 99]'),
                'expectedMessage' => 'Expected tuple<string, int> of length 2, got 3',
            ],
            'wrong type at position 0' => [
                'parser' => $stringInt,
                'data' => json_decode('[42, 42]'),
                'expectedMessage' => "Failed to parse:\n  at [0]: Expected string, got int",
            ],
            'wrong type at position 1' => [
                'parser' => $stringInt,
                'data' => json_decode('["foo", "bar"]'),
                'expectedMessage' => "Failed to parse:\n  at [1]: Expected int, got string",
            ],
            'both positions wrong type' => [
                'parser' => $stringInt,
                'data' => json_decode('[42, "bar"]'),
                'expectedMessage' => "Failed to parse:\n  at [0]: Expected string, got int\n  at [1]: Expected int, got string",
            ],
        ];
    }

    public function test_lenient_tuple_returns_null_on_failure(): void
    {
        // Arrange
        $parser = Modifiers::lenient(new TupleParser(new StringParser, new IntegerParser));

        // Act + Assert
        $this->assertSame(['foo', 42], $parser->parse(json_decode('["foo", 42]')));
        $this->assertNull($parser->parse(json_decode('["foo", "bar"]')));
        $this->assertNull($parser->parse(json_decode('["foo"]')));
    }

    public function test_nullable_tuple_allows_null(): void
    {
        // Arrange
        $parser = Modifiers::nullable(new TupleParser(new StringParser, new IntegerParser));

        // Act + Assert
        $this->assertNull($parser->parse(null));
        $this->assertSame(['foo', 42], $parser->parse(json_decode('["foo", 42]')));
    }

    public function test_transform_tuple_to_associative_array(): void
    {
        // Arrange
        $parser = Modifiers::transform(
            new TupleParser(new StringParser, new IntegerParser),
            fn (array $t): array => ['name' => $t[0], 'age' => $t[1]],
        );

        // Act
        $result = $parser->parse(json_decode('["alice", 30]'));

        // Assert
        $this->assertSame(['name' => 'alice', 'age' => 30], $result);
    }
}
