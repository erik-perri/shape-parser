<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\BooleanParser;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\OptionalParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(OptionalParser::class)]
class OptionalParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse(ParserContract $inner, mixed $input, mixed $expected): void
    {
        // Arrange
        $parser = $inner->optional();

        // Act
        $result = $parser->parse($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{inner: ParserContract<mixed>, input: mixed, expected: mixed}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'string valid' => [
                'inner' => new StringParser,
                'input' => 'hello',
                'expected' => 'hello',
            ],
            'integer valid' => [
                'inner' => new IntegerParser,
                'input' => 42,
                'expected' => 42,
            ],
            'boolean valid' => [
                'inner' => new BooleanParser,
                'input' => true,
                'expected' => true,
            ],
            'nullable inner with null' => [
                'inner' => (new StringParser)->nullable(),
                'input' => null,
                'expected' => null,
            ],
        ];
    }

    #[DataProvider('invalidCasesProvider')]
    public function test_parse_throws_on_invalid_input(ParserContract $inner, mixed $input): void
    {
        // Expectations
        $this->expectException(ParseException::class);

        // Arrange
        $parser = $inner->optional();

        // Act
        $parser->parse($input);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{inner: ParserContract<mixed>, input: mixed}>
     */
    public static function invalidCasesProvider(): array
    {
        return [
            'string given int' => [
                'inner' => new StringParser,
                'input' => 123,
            ],
            'string given null' => [
                'inner' => new StringParser,
                'input' => null,
            ],
            'integer given string' => [
                'inner' => new IntegerParser,
                'input' => 'hello',
            ],
        ];
    }

    #[DataProvider('describeCasesProvider')]
    public function test_describe(ParserContract $inner, string $expected): void
    {
        // Arrange
        $parser = $inner->optional();

        // Act
        $description = $parser->describe();

        // Assert
        $this->assertSame($expected, $description);
    }

    /**
     * @return array<string, array{inner: ParserContract<mixed>, expected: string}>
     */
    public static function describeCasesProvider(): array
    {
        return [
            'string' => [
                'inner' => new StringParser,
                'expected' => 'optional<string>',
            ],
            'nullable integer' => [
                'inner' => (new IntegerParser)->nullable(),
                'expected' => 'optional<nullable<int>>',
            ],
            'list of string' => [
                'inner' => new ListParser(new StringParser),
                'expected' => 'optional<list<string>>',
            ],
        ];
    }

    public function test_double_optional_throws(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call optional() on an already optional parser.');

        // Arrange
        $parser = (new StringParser)->optional();

        // Act
        $parser->optional();

        // Assert
        // No assertions, only expectations.
    }

    public function test_nullable_after_optional_throws(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call nullable() on an optional parser');

        // Arrange
        $parser = (new StringParser)->optional();

        // Act
        $parser->nullable();

        // Assert
        // No assertions, only expectations.
    }

    public function test_lenient_after_optional_throws(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call lenient() on an optional parser');

        // Arrange
        $parser = (new StringParser)->optional();

        // Act
        $parser->lenient();

        // Assert
        // No assertions, only expectations.
    }

    public function test_transform_after_optional_throws(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call transform() on an optional parser');

        // Arrange
        $parser = (new StringParser)->optional();

        // Act
        $parser->transform(fn (string $s): string => $s);

        // Assert
        // No assertions, only expectations.
    }
}
