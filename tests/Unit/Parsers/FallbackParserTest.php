<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\FallbackParser;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(FallbackParser::class)]
class FallbackParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse(ParserContract $inner, mixed $fallback, mixed $input, mixed $expected): void
    {
        // Arrange
        $fallbackParser = $inner->lenient()->fallback($fallback);

        // Act
        $result = $fallbackParser->parse($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{inner: ParserContract<mixed>, fallback: mixed, input: mixed, expected: mixed}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'string success' => [
                'inner' => new StringParser,
                'fallback' => 'fallback',
                'input' => 'hello',
                'expected' => 'hello',
            ],
            'string failure' => [
                'inner' => new StringParser,
                'fallback' => 'fallback',
                'input' => 123,
                'expected' => 'fallback',
            ],
            'integer failure' => [
                'inner' => new IntegerParser,
                'fallback' => 0,
                'input' => 'not an int',
                'expected' => 0,
            ],
        ];
    }

    #[DataProvider('safeParseCasesProvider')]
    public function test_safe_parse_returns_success(mixed $input, mixed $expected): void
    {
        // Arrange
        $parser = new StringParser;
        $fallbackParser = $parser->lenient()->fallback('fallback');

        // Act
        $result = $fallbackParser->safeParse($input);

        // Assert
        $this->assertTrue($result->success);
        $this->assertSame($expected, $result->data);
        $this->assertNull($result->error);
    }

    /**
     * @return array<string, array{input: mixed, expected: mixed}>
     */
    public static function safeParseCasesProvider(): array
    {
        return [
            'valid input' => [
                'input' => 'hello',
                'expected' => 'hello',
            ],
            'invalid input uses fallback' => [
                'input' => 123,
                'expected' => 'fallback',
            ],
        ];
    }

    #[DataProvider('describeCasesProvider')]
    public function test_describe(ParserContract $inner, mixed $fallback, string $expected): void
    {
        // Arrange
        $fallbackParser = $inner->lenient()->fallback($fallback);

        // Act
        $description = $fallbackParser->describe();

        // Assert
        $this->assertSame($expected, $description);
    }

    /**
     * @return array<string, array{inner: ParserContract<mixed>, fallback: mixed, expected: string}>
     */
    public static function describeCasesProvider(): array
    {
        return [
            'string fallback' => [
                'inner' => new StringParser,
                'fallback' => 'fallback',
                'expected' => "fallback<string, 'fallback'>",
            ],
            'integer fallback' => [
                'inner' => new IntegerParser,
                'fallback' => 42,
                'expected' => 'fallback<int, 42>',
            ],
            'boolean fallback' => [
                'inner' => new StringParser,
                'fallback' => false,
                'expected' => 'fallback<string, false>',
            ],
            'complex fallback' => [
                'inner' => new ObjectParser(['name' => new StringParser]),
                'fallback' => ['name' => 'unknown'],
                'expected' => 'fallback<object, array>',
            ],
        ];
    }
}
