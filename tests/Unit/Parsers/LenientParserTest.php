<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Parsers\LenientParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(LenientParser::class)]
class LenientParserTest extends TestCase
{
    public function testParseReturnsResultOnSuccess(): void
    {
        // Arrange
        $parser = new StringParser();
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse('hello');

        // Assert
        $this->assertSame('hello', $result);
    }

    public function testParseReturnsNullOnFailure(): void
    {
        // Arrange
        $parser = new StringParser();
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse(123);

        // Assert
        $this->assertNull($result);
    }

    public function testDescribeReturnsLenientWrappedDescription(): void
    {
        // Arrange
        $parser = new StringParser();
        $lenientParser = $parser->lenient();

        // Act
        $description = $lenientParser->describe();

        // Assert
        $this->assertSame('lenient<string>', $description);
    }

    public function testDoubleLenientThrows(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call lenient() on an already lenient parser.');

        // Arrange
        $parser = new StringParser();
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->lenient();

        // Assert
        // No assertions, only expectations.
    }

    public function testNullableAfterLenientThrows(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call nullable() on an already lenient parser.');

        // Arrange
        $parser = new StringParser();
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->nullable();

        // Assert
        // No assertions, only expectations.
    }
}
