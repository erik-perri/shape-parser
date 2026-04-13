<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\BaseParser;
use Sourcetoad\ShapeParser\Parsers\BooleanParser;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\OptionalParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(OptionalParser::class)]
class OptionalParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse(BaseParser $inner, mixed $input, mixed $expected): void
    {
        // Arrange
        $parser = Modifiers::optional($inner);

        // Act
        $result = $parser->parse($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{inner: BaseParser<mixed>, input: mixed, expected: mixed}>
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
                'inner' => Modifiers::nullable(new StringParser),
                'input' => null,
                'expected' => null,
            ],
        ];
    }

    #[DataProvider('invalidCasesProvider')]
    public function test_parse_throws_on_invalid_input(BaseParser $inner, mixed $input, string $expectedMessage): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        // Arrange
        $parser = Modifiers::optional($inner);

        // Act
        $parser->parse($input);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{inner: BaseParser<mixed>, input: mixed}>
     */
    public static function invalidCasesProvider(): array
    {
        return [
            'string given int' => [
                'inner' => new StringParser,
                'input' => 123,
                'expectedMessage' => 'Expected string, got int',
            ],
            'string given null' => [
                'inner' => new StringParser,
                'input' => null,
                'expectedMessage' => 'Expected string, got null',
            ],
            'integer given string' => [
                'inner' => new IntegerParser,
                'input' => 'hello',
                'expectedMessage' => 'Expected int, got string',
            ],
        ];
    }

    #[DataProvider('describeCasesProvider')]
    public function test_describe(BaseParser $inner, string $expected): void
    {
        // Arrange
        $parser = Modifiers::optional($inner);

        // Act
        $description = $parser->describe();

        // Assert
        $this->assertSame($expected, $description);
    }

    /**
     * @return array<string, array{inner: BaseParser<mixed>, expected: string}>
     */
    public static function describeCasesProvider(): array
    {
        return [
            'string' => [
                'inner' => new StringParser,
                'expected' => 'optional<string>',
            ],
            'nullable integer' => [
                'inner' => Modifiers::nullable(new IntegerParser),
                'expected' => 'optional<nullable<int>>',
            ],
            'list of string' => [
                'inner' => new ListParser(new StringParser),
                'expected' => 'optional<list<string>>',
            ],
        ];
    }

    public function test_is_optional_is_true_on_direct_optional(): void
    {
        $parser = Modifiers::optional(new StringParser);

        $this->assertTrue($parser->isOptional());
    }

    public function test_is_optional_propagates_through_fallback(): void
    {
        $parser = Modifiers::fallback(Modifiers::optional(new StringParser), 'default');

        $this->assertTrue($parser->isOptional());
    }

    public function test_is_optional_propagates_through_nullable(): void
    {
        $parser = Modifiers::nullable(Modifiers::optional(new StringParser));

        $this->assertTrue($parser->isOptional());
    }
}
