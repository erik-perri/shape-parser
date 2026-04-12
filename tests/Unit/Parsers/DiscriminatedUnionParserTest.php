<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\DiscriminatedUnionParser;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\LiteralParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(DiscriminatedUnionParser::class)]
class DiscriminatedUnionParserTest extends TestCase
{
    public function testParseReturnsResult(): void
    {
        // Arrange
        $parser = new DiscriminatedUnionParser('type', [
            new ObjectParser(['type' => new LiteralParser('foo'), 'value' => new StringParser()]),
            new ObjectParser(['type' => new LiteralParser('bar'), 'bar' => new IntegerParser()]),
        ]);
        $data = json_decode('{"type": "foo", "value": "bar"}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['type' => 'foo', 'value' => 'bar'], $result);
    }

    public function testParseThrowsWhenInvalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new DiscriminatedUnionParser('type', [
            new ObjectParser(['type' => new LiteralParser('foo'), 'value' => new StringParser()]),
            new ObjectParser(['type' => new LiteralParser('bar'), 'bar' => new IntegerParser()]),
        ]);
        $data = json_decode('{"type": "foo", "value": 123}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function testParseAcceptsTransformParserVariant(): void
    {
        // Arrange
        $fooVariant = (new ObjectParser([
            'type' => new LiteralParser('foo'),
            'value' => new StringParser(),
        ]))->transform(fn(array $a) => strtoupper($a['value']));
        $barVariant = (new ObjectParser([
            'type' => new LiteralParser('bar'),
            'bar' => new IntegerParser(),
        ]))->transform(fn(array $a) => $a['bar'] * 2);

        $parser = new DiscriminatedUnionParser('type', [$fooVariant, $barVariant]);

        // Act
        $fooResult = $parser->parse(['type' => 'foo', 'value' => 'hi']);
        $barResult = $parser->parse(['type' => 'bar', 'bar' => 21]);

        // Assert
        $this->assertSame('HI', $fooResult);
        $this->assertSame(42, $barResult);
    }

    public function testParseAcceptsChainedTransformParserVariant(): void
    {
        // Arrange
        $variant = (new ObjectParser([
            'type' => new LiteralParser('foo'),
            'value' => new IntegerParser(),
        ]))
            ->transform(fn(array $a) => $a['value'] + 1)
            ->transform(fn(int $i) => $i * 10);

        $parser = new DiscriminatedUnionParser('type', [$variant]);

        // Act
        $result = $parser->parse(['type' => 'foo', 'value' => 4]);

        // Assert
        $this->assertSame(50, $result);
    }

    public function testConstructorRejectsBareStringParserVariant(): void
    {
        // Expectations
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be an ObjectParser (optionally wrapped in TransformParser)');

        // Arrange + Act
        new DiscriminatedUnionParser('type', [
            new StringParser(),
        ]);

        // Assert
        // No assertions, only expectations.
    }

    public function testConstructorRejectsNullableObjectParserVariant(): void
    {
        // Expectations
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be an ObjectParser (optionally wrapped in TransformParser)');

        // Arrange
        $wrapped = (new ObjectParser([
            'type' => new LiteralParser('foo'),
        ]))->nullable();

        // Act
        new DiscriminatedUnionParser('type', [$wrapped]);

        // Assert
        // No assertions, only expectations.
    }
}