<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

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
}