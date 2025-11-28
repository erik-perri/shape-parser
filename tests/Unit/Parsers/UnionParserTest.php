<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;
use Sourcetoad\ShapeParser\Parsers\UnionParser;

#[CoversClass(UnionParser::class)]
class UnionParserTest extends TestCase
{
    public function testParseReturnsResult(): void
    {
        // Arrange
        $parser = new UnionParser(new IntegerParser(), new StringParser());
        $data = json_decode('"foo"');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame('foo', $result);
    }

    public function testParseThrowsWhenInvalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected int|string, got bool');

        // Arrange
        $parser = new UnionParser(new IntegerParser(), new StringParser());
        $data = json_decode('false');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}