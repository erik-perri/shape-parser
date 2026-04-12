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
use Sourcetoad\ShapeParser\Parsers\NullableParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(NullableParser::class)]
class NullableParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse(ParserContract $inner, mixed $input, mixed $expected): void
    {
        // Arrange
        $parser = $inner->nullable();

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
            'string null' => [
                'inner' => new StringParser,
                'input' => null,
                'expected' => null,
            ],
            'integer valid' => [
                'inner' => new IntegerParser,
                'input' => 42,
                'expected' => 42,
            ],
            'integer null' => [
                'inner' => new IntegerParser,
                'input' => null,
                'expected' => null,
            ],
            'boolean valid' => [
                'inner' => new BooleanParser,
                'input' => true,
                'expected' => true,
            ],
            'boolean null' => [
                'inner' => new BooleanParser,
                'input' => null,
                'expected' => null,
            ],
        ];
    }

    #[DataProvider('invalidCasesProvider')]
    public function test_parse_throws_on_invalid_non_null(ParserContract $inner, mixed $input): void
    {
        // Expectations
        $this->expectException(ParseException::class);

        // Arrange
        $parser = $inner->nullable();

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
            'integer given string' => [
                'inner' => new IntegerParser,
                'input' => 'hello',
            ],
            'boolean given string' => [
                'inner' => new BooleanParser,
                'input' => 'not-a-bool',
            ],
        ];
    }

    #[DataProvider('describeCasesProvider')]
    public function test_describe(ParserContract $inner, string $expected): void
    {
        // Arrange
        $parser = $inner->nullable();

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
                'expected' => 'nullable<string>',
            ],
            'integer' => [
                'inner' => new IntegerParser,
                'expected' => 'nullable<int>',
            ],
            'list of string' => [
                'inner' => new ListParser(new StringParser),
                'expected' => 'nullable<list<string>>',
            ],
        ];
    }

    public function test_double_nullable_throws(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call nullable() on an already nullable parser.');

        // Arrange
        $parser = (new StringParser)->nullable();

        // Act
        $parser->nullable();

        // Assert
        // No assertions, only expectations.
    }

    public function test_nullable_lenient_chain_is_allowed(): void
    {
        // Arrange
        $parser = (new StringParser)->nullable()->lenient();

        // Act + Assert
        $this->assertNull($parser->parse(null));
        $this->assertNull($parser->parse(123));
        $this->assertSame('hello', $parser->parse('hello'));
    }
}
