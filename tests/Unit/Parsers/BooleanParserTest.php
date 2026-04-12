<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\BooleanParser;

#[CoversClass(BooleanParser::class)]
class BooleanParserTest extends TestCase
{
    public function testParseReturnsTrueResult(): void
    {
        // Arrange
        $parser = new BooleanParser();
        $data = json_decode('true');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertTrue($result);
    }

    public function testParseReturnsFalseResult(): void
    {
        // Arrange
        $parser = new BooleanParser();
        $data = json_decode('false');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertFalse($result);
    }

    public function testParseThrowsWhenInvalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected bool, got int');

        // Arrange
        $parser = new BooleanParser();
        $data = json_decode('1');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}
