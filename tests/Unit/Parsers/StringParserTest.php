<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(StringParser::class)]
class StringParserTest extends TestCase
{
    public function testParseReturnsResult(): void
    {
        // Arrange
        $parser = new StringParser();
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
        $this->expectExceptionMessage('Expected string, got int');

        // Arrange
        $parser = new StringParser();
        $data = json_decode('123');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}