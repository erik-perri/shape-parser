<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\NumberParser;

#[CoversClass(NumberParser::class)]
class NumberParserTest extends TestCase
{
    public function testParseReturnsIntegerResult(): void
    {
        // Arrange
        $parser = new NumberParser();
        $data = json_decode('1');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(1, $result);
    }

    public function testParseReturnsFloatResult(): void
    {
        // Arrange
        $parser = new NumberParser();
        $data = json_decode('1.5');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(1.5, $result);
    }

    public function testParseThrowsWhenGivenString(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected number, got string');

        // Arrange
        $parser = new NumberParser();
        $data = json_decode('"1"');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}
