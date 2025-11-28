<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\RecordParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(RecordParser::class)]
class RecordParserTest extends TestCase
{
    public function testParseReturnsResult(): void
    {
        // Arrange
        $parser = new RecordParser(new StringParser(), new ObjectParser([
            'foo' => new StringParser(),
        ]));
        $data = json_decode('{"a": {"foo": "bar"}, "b": {"foo": "baz"}}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['a' => ['foo' => 'bar'], 'b' => ['foo' => 'baz']], $result);
    }

    public function testParseThrowsWhenKeysAreWrongType(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse record');

        // Arrange
        $parser = new RecordParser(new StringParser(), new ObjectParser([
            'foo' => new StringParser(),
        ]));
        $data = json_decode('{"0": {"foo": "bar"}, "1": {"foo": "baz"}}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function testParseThrowsWhenTypeIsWrong(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected record, got string');

        // Arrange
        $parser = new RecordParser(new StringParser(), new StringParser());
        $data = json_decode('"foo"');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}