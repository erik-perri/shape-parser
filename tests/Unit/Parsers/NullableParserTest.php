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
    public function testParse(ParserContract $inner, mixed $input, mixed $expected): void
    {
        // Arrange
        $parser = $inner->nullable();

        // Act
        $result = $parser->parse($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{ParserContract<mixed>, mixed, mixed}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'string valid' => [new StringParser(), 'hello', 'hello'],
            'string null' => [new StringParser(), null, null],
            'integer valid' => [new IntegerParser(), 42, 42],
            'integer null' => [new IntegerParser(), null, null],
            'boolean valid' => [new BooleanParser(), true, true],
            'boolean null' => [new BooleanParser(), null, null],
        ];
    }

    #[DataProvider('invalidCasesProvider')]
    public function testParseThrowsOnInvalidNonNull(ParserContract $inner, mixed $input): void
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
     * @return array<string, array{ParserContract<mixed>, mixed}>
     */
    public static function invalidCasesProvider(): array
    {
        return [
            'string given int' => [new StringParser(), 123],
            'integer given string' => [new IntegerParser(), 'hello'],
            'boolean given string' => [new BooleanParser(), 'not-a-bool'],
        ];
    }

    #[DataProvider('describeCasesProvider')]
    public function testDescribe(ParserContract $inner, string $expected): void
    {
        // Arrange
        $parser = $inner->nullable();

        // Act
        $description = $parser->describe();

        // Assert
        $this->assertSame($expected, $description);
    }

    /**
     * @return array<string, array{ParserContract<mixed>, string}>
     */
    public static function describeCasesProvider(): array
    {
        return [
            'string' => [new StringParser(), 'nullable<string>'],
            'integer' => [new IntegerParser(), 'nullable<int>'],
            'list of string' => [new ListParser(new StringParser()), 'nullable<list<string>>'],
        ];
    }

    public function testDoubleNullableThrows(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call nullable() on an already nullable parser.');

        // Arrange
        $parser = (new StringParser())->nullable();

        // Act
        $parser->nullable();

        // Assert
        // No assertions, only expectations.
    }

    public function testNullableLenientChainIsAllowed(): void
    {
        // Arrange
        $parser = (new StringParser())->nullable()->lenient();

        // Act + Assert
        $this->assertNull($parser->parse(null));
        $this->assertNull($parser->parse(123));
        $this->assertSame('hello', $parser->parse('hello'));
    }
}
