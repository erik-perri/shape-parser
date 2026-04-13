<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Data\ParseIssue;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(ObjectParser::class)]
class ObjectParserTest extends TestCase
{
    public function test_parse_returns_result(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser,
            'bar' => new ObjectParser([
                'baz' => new IntegerParser,
            ]),
        ]);
        $data = json_decode('{"foo": "bar", "bar": {"baz": 123}}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['foo' => 'bar', 'bar' => ['baz' => 123]], $result);
    }

    #[DataProvider('parseCasesProvider')]
    public function test_parse(ObjectParser $parser, mixed $data, array $expected): void
    {
        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{parser: ObjectParser, data: mixed, expected: array<string, mixed>}>
     */
    public static function parseCasesProvider(): array
    {
        $optionalSchema = new ObjectParser([
            'foo' => new StringParser,
            'bar' => Modifiers::optional(new StringParser),
        ]);
        $nullableOptionalSchema = new ObjectParser([
            'foo' => Modifiers::optional(Modifiers::nullable(new StringParser)),
        ]);

        return [
            'optional field omitted when absent' => [
                'parser' => $optionalSchema,
                'data' => json_decode('{"foo": "hello"}'),
                'expected' => ['foo' => 'hello'],
            ],
            'optional field included when present' => [
                'parser' => $optionalSchema,
                'data' => json_decode('{"foo": "hello", "bar": "world"}'),
                'expected' => ['foo' => 'hello', 'bar' => 'world'],
            ],
            'nullable optional field absent' => [
                'parser' => $nullableOptionalSchema,
                'data' => json_decode('{}'),
                'expected' => [],
            ],
            'nullable optional field null' => [
                'parser' => $nullableOptionalSchema,
                'data' => json_decode('{"foo": null}'),
                'expected' => ['foo' => null],
            ],
            'nullable optional field with value' => [
                'parser' => $nullableOptionalSchema,
                'data' => json_decode('{"foo": "hi"}'),
                'expected' => ['foo' => 'hi'],
            ],
        ];
    }

    #[DataProvider('invalidCasesProvider')]
    public function test_parse_throws_when_invalid(ObjectParser $parser, mixed $data, string $expectedMessage): void
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
     * @return array<string, array{parser: ObjectParser, data: mixed, expectedMessage: list<ParseIssue>}>
     */
    public static function invalidCasesProvider(): array
    {
        return [
            'required field missing' => [
                'parser' => new ObjectParser([
                    'foo' => new StringParser,
                ]),
                'data' => json_decode('{"bar": "foo"}'),
                'expectedMessage' => "Failed to parse:\n  at [foo]: Missing required field",
            ],
            'optional field present but wrong type' => [
                'parser' => new ObjectParser([
                    'foo' => Modifiers::optional(new StringParser),
                ]),
                'data' => json_decode('{"foo": 123}'),
                'expectedMessage' => "Failed to parse:\n  at [foo]: Expected string, got int",
            ],
            'optional field present but null on non-nullable inner' => [
                'parser' => new ObjectParser([
                    'foo' => Modifiers::optional(new StringParser),
                ]),
                'data' => json_decode('{"foo": null}'),
                'expectedMessage' => "Failed to parse:\n  at [foo]: Expected string, got null",
            ],
            'required field still fails when optional fields also absent' => [
                'parser' => new ObjectParser([
                    'foo' => new StringParser,
                    'bar' => Modifiers::optional(new StringParser),
                ]),
                'data' => json_decode('{}'),
                'expectedMessage' => "Failed to parse:\n  at [foo]: Missing required field",
            ],
            'multiple bad fields aggregated' => [
                'parser' => new ObjectParser([
                    'foo' => new StringParser,
                    'bar' => new IntegerParser,
                ]),
                'data' => json_decode('{"foo": 123, "bar": "oops"}'),
                'expectedMessage' => "Failed to parse:\n  at [foo]: Expected string, got int\n  at [bar]: Expected int, got string",
            ],
            'nested object failure reports full path' => [
                'parser' => new ObjectParser([
                    'outer' => new ObjectParser([
                        'inner' => new StringParser,
                    ]),
                ]),
                'data' => json_decode('{"outer": {"inner": 5}}'),
                'expectedMessage' => "Failed to parse:\n  at [outer][inner]: Expected string, got int",
            ],
            'list of objects reports index and key paths' => [
                'parser' => new ObjectParser([
                    'users' => new ListParser(new ObjectParser([
                        'email' => new StringParser,
                    ])),
                ]),
                'data' => json_decode('{"users": [{"email": "ok@example.com"}, {"email": 5}, {"email": null}]}'),
                'expectedMessage' => "Failed to parse:\n  at [users][1][email]: Expected string, got int\n  at [users][2][email]: Expected string, got null",
            ],
        ];
    }
}
