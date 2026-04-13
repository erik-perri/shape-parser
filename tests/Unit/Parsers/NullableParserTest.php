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
use Sourcetoad\ShapeParser\Parsers\NullableParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(NullableParser::class)]
class NullableParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse(BaseParser $inner, mixed $input, mixed $expected): void
    {
        // Arrange
        $parser = Modifiers::nullable($inner);

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
    public function test_parse_throws_on_invalid_non_null(
        BaseParser $inner,
        mixed $input,
        string $expectedMessage,
    ): void {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        // Arrange
        $parser = Modifiers::nullable($inner);

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
            'integer given string' => [
                'inner' => new IntegerParser,
                'input' => 'hello',
                'expectedMessage' => 'Expected int, got string',
            ],
            'boolean given string' => [
                'inner' => new BooleanParser,
                'input' => 'not-a-bool',
                'expectedMessage' => 'Expected bool, got string',
            ],
        ];
    }

    #[DataProvider('describeCasesProvider')]
    public function test_describe(BaseParser $inner, string $expected): void
    {
        // Arrange
        $parser = Modifiers::nullable($inner);

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

    public function test_nullable_lenient_chain_is_allowed(): void
    {
        // Arrange
        $parser = Modifiers::lenient(Modifiers::nullable(new StringParser));

        // Act + Assert
        $this->assertNull($parser->parse(null));
        $this->assertNull($parser->parse(123));
        $this->assertSame('hello', $parser->parse('hello'));
    }

    public function test_nullable_wrapping_optional_propagates_optional(): void
    {
        // Arrange
        $parser = Modifiers::nullable(Modifiers::optional(new StringParser));

        // Act + Assert
        $this->assertTrue($parser->isOptional());
        $this->assertNull($parser->parse(null));
        $this->assertSame('hello', $parser->parse('hello'));
    }
}
