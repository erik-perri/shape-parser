<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\LenientListParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(LenientListParser::class)]
class LenientListParserTest extends TestCase
{
    public function testParseReturnsAllItemsWhenValid(): void
    {
        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse(json_decode('["foo", "bar", "baz"]'));

        // Assert
        $this->assertSame(['foo', 'bar', 'baz'], $result);
    }

    public function testParseDropsInvalidItems(): void
    {
        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse(['a', 123, 'b', true, 'c']);

        // Assert
        $this->assertSame(['a', 'b', 'c'], $result);
    }

    public function testParseReturnsEmptyListWhenAllItemsFail(): void
    {
        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse([1, 2, 3]);

        // Assert
        $this->assertSame([], $result);
    }

    public function testParseThrowsWhenInputIsNotArray(): void
    {
        // Expectations
        $this->expectException(ParseException::class);

        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->parse('not an array');

        // Assert
        // No assertions, only expectations.
    }

    public function testParseThrowsWhenInputIsNotList(): void
    {
        // Expectations
        $this->expectException(ParseException::class);

        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->parse(['a' => 'foo', 'b' => 'bar']);

        // Assert
        // No assertions, only expectations.
    }

    public function testDescribeReturnsLenientWrappedDescription(): void
    {
        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $description = $lenientParser->describe();

        // Assert
        $this->assertSame('lenient<list<string>>', $description);
    }

    public function testDoubleLenientThrows(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call lenient() on an already lenient parser.');

        // Arrange
        $parser = new ListParser(new StringParser());
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->lenient();

        // Assert
        // No assertions, only expectations.
    }
}
