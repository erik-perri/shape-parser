<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Parsers\FallbackParser;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(FallbackParser::class)]
class FallbackParserTest extends TestCase
{
    public function testParseReturnsResultOnSuccess(): void
    {
        // Arrange
        $parser = new StringParser();
        $fallbackParser = $parser->lenient()->fallback('fallback');

        // Act
        $result = $fallbackParser->parse('hello');

        // Assert
        $this->assertSame('hello', $result);
    }

    public function testParseReturnsFallbackOnFailure(): void
    {
        // Arrange
        $parser = new StringParser();
        $fallbackParser = $parser->lenient()->fallback('fallback');

        // Act
        $result = $fallbackParser->parse(123);

        // Assert
        $this->assertSame('fallback', $result);
    }

    public function testParseReturnsIntegerFallbackOnFailure(): void
    {
        // Arrange
        $parser = new IntegerParser();
        $fallbackParser = $parser->lenient()->fallback(0);

        // Act
        $result = $fallbackParser->parse('not an int');

        // Assert
        $this->assertSame(0, $result);
    }

    public function testSafeParseAlwaysReturnsSuccess(): void
    {
        // Arrange
        $parser = new StringParser();
        $fallbackParser = $parser->lenient()->fallback('fallback');

        // Act
        $result = $fallbackParser->safeParse(123);

        // Assert
        $this->assertTrue($result->success);
        $this->assertSame('fallback', $result->data);
        $this->assertNull($result->error);
    }

    public function testSafeParseReturnsDataOnSuccess(): void
    {
        // Arrange
        $parser = new StringParser();
        $fallbackParser = $parser->lenient()->fallback('fallback');

        // Act
        $result = $fallbackParser->safeParse('hello');

        // Assert
        $this->assertTrue($result->success);
        $this->assertSame('hello', $result->data);
        $this->assertNull($result->error);
    }

    public function testDescribeWithStringFallback(): void
    {
        // Arrange
        $parser = new StringParser();
        $fallbackParser = $parser->lenient()->fallback('fallback');

        // Act
        $description = $fallbackParser->describe();

        // Assert
        $this->assertSame("fallback<string, 'fallback'>", $description);
    }

    public function testDescribeWithIntegerFallback(): void
    {
        // Arrange
        $parser = new IntegerParser();
        $fallbackParser = $parser->lenient()->fallback(42);

        // Act
        $description = $fallbackParser->describe();

        // Assert
        $this->assertSame('fallback<int, 42>', $description);
    }

    public function testDescribeWithBooleanFallback(): void
    {
        // Arrange
        $parser = new StringParser();
        $fallbackParser = $parser->lenient()->fallback(false);

        // Act
        $description = $fallbackParser->describe();

        // Assert
        $this->assertSame('fallback<string, false>', $description);
    }

    public function testDescribeWithComplexFallback(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'name' => new StringParser(),
        ]);
        $fallbackParser = $parser->lenient()->fallback(['name' => 'unknown']);

        // Act
        $description = $fallbackParser->describe();

        // Assert
        $this->assertSame('fallback<object, array>', $description);
    }
}
