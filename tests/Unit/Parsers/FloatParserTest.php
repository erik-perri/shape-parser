<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\FloatParser;

#[CoversClass(FloatParser::class)]
class FloatParserTest extends TestCase
{
    public function testParseReturnsFloatResult(): void
    {
        // Arrange
        $parser = new FloatParser();
        $data = json_decode('1.5');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(1.5, $result);
    }

    public function testParseThrowsWhenGivenInteger(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected float, got int');

        // Arrange
        $parser = new FloatParser();
        $data = json_decode('1');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function testParseThrowsWhenGivenString(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected float, got string');

        // Arrange
        $parser = new FloatParser();
        $data = json_decode('"1.5"');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}
