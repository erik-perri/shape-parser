<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\LenientRecordParser;
use Sourcetoad\ShapeParser\Parsers\RecordParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(LenientRecordParser::class)]
class LenientRecordParserTest extends TestCase
{
    public function testParseReturnsAllEntriesWhenValid(): void
    {
        // Arrange
        $parser = new RecordParser(new StringParser(), new IntegerParser());
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse(json_decode('{"a": 1, "b": 2, "c": 3}'));

        // Assert
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    public function testParseDropsEntriesWithInvalidValues(): void
    {
        // Arrange
        $parser = new RecordParser(new StringParser(), new IntegerParser());
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse(['a' => 1, 'b' => 'not int', 'c' => 3]);

        // Assert
        $this->assertSame(['a' => 1, 'c' => 3], $result);
    }

    public function testParseReturnsEmptyArrayWhenAllEntriesFail(): void
    {
        // Arrange
        $parser = new RecordParser(new StringParser(), new IntegerParser());
        $lenientParser = $parser->lenient();

        // Act
        $result = $lenientParser->parse(['a' => 'x', 'b' => 'y']);

        // Assert
        $this->assertSame([], $result);
    }

    public function testParseThrowsWhenInputIsNotArray(): void
    {
        // Expectations
        $this->expectException(ParseException::class);

        // Arrange
        $parser = new RecordParser(new StringParser(), new IntegerParser());
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->parse('not an array');

        // Assert
        // No assertions, only expectations.
    }

    public function testDescribeReturnsLenientWrappedDescription(): void
    {
        // Arrange
        $parser = new RecordParser(new StringParser(), new IntegerParser());
        $lenientParser = $parser->lenient();

        // Act
        $description = $lenientParser->describe();

        // Assert
        $this->assertSame('lenient<record<string, int>>', $description);
    }

    public function testDoubleLenientThrows(): void
    {
        // Expectations
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call lenient() on an already lenient parser.');

        // Arrange
        $parser = new RecordParser(new StringParser(), new IntegerParser());
        $lenientParser = $parser->lenient();

        // Act
        $lenientParser->lenient();

        // Assert
        // No assertions, only expectations.
    }
}
