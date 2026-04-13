<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\DiscriminatedUnionParser;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\LiteralParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(DiscriminatedUnionParser::class)]
class DiscriminatedUnionParserTest extends TestCase
{
    public function test_parse_returns_result(): void
    {
        // Arrange
        $parser = new DiscriminatedUnionParser('type', [
            new ObjectParser(['type' => new LiteralParser('foo'), 'value' => new StringParser]),
            new ObjectParser(['type' => new LiteralParser('bar'), 'bar' => new IntegerParser]),
        ]);
        $data = json_decode('{"type": "foo", "value": "bar"}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['type' => 'foo', 'value' => 'bar'], $result);
    }

    public function test_parse_throws_when_invalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage("Failed to parse:\n  at [value]: Expected string, got int");

        // Arrange
        $parser = new DiscriminatedUnionParser('type', [
            new ObjectParser(['type' => new LiteralParser('foo'), 'value' => new StringParser]),
            new ObjectParser(['type' => new LiteralParser('bar'), 'bar' => new IntegerParser]),
        ]);
        $data = json_decode('{"type": "foo", "value": 123}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function test_parse_accepts_transform_parser_variant(): void
    {
        // Arrange
        $fooVariant = Modifiers::transform(
            new ObjectParser([
                'type' => new LiteralParser('foo'),
                'value' => new StringParser,
            ]),
            fn (array $a) => strtoupper($a['value']),
        );
        $barVariant = Modifiers::transform(
            new ObjectParser([
                'type' => new LiteralParser('bar'),
                'bar' => new IntegerParser,
            ]),
            fn (array $a) => $a['bar'] * 2,
        );

        $parser = new DiscriminatedUnionParser('type', [$fooVariant, $barVariant]);

        // Act
        $fooResult = $parser->parse(['type' => 'foo', 'value' => 'hi']);
        $barResult = $parser->parse(['type' => 'bar', 'bar' => 21]);

        // Assert
        $this->assertSame('HI', $fooResult);
        $this->assertSame(42, $barResult);
    }

    public function test_parse_accepts_chained_transform_parser_variant(): void
    {
        // Arrange
        $variant = Modifiers::transform(
            Modifiers::transform(
                new ObjectParser([
                    'type' => new LiteralParser('foo'),
                    'value' => new IntegerParser,
                ]),
                fn (array $a) => $a['value'] + 1,
            ),
            fn (int $i) => $i * 10,
        );

        $parser = new DiscriminatedUnionParser('type', [$variant]);

        // Act
        $result = $parser->parse(['type' => 'foo', 'value' => 4]);

        // Assert
        $this->assertSame(50, $result);
    }

    #[DataProvider('invalidVariantProvider')]
    public function test_constructor_rejects_invalid_variant(ParserContract $variant): void
    {
        // Expectations
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be an ObjectParser (optionally wrapped in TransformParser)');

        // Arrange + Act
        new DiscriminatedUnionParser('type', [$variant]);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{variant: ParserContract<mixed>}>
     */
    public static function invalidVariantProvider(): array
    {
        return [
            'bare string parser' => [
                'variant' => new StringParser,
            ],
            'nullable object parser' => [
                'variant' => Modifiers::nullable(new ObjectParser([
                    'type' => new LiteralParser('foo'),
                ])),
            ],
        ];
    }
}
