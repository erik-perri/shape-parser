<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Parsers\LenientParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(LenientParser::class)]
class LenientParserTest extends TestCase
{
    public function test_parse_returns_result_on_success(): void
    {
        // Arrange
        $lenientParser = Modifiers::lenient(new StringParser);

        // Act
        $result = $lenientParser->parse('hello');

        // Assert
        $this->assertSame('hello', $result);
    }

    public function test_parse_returns_null_on_failure(): void
    {
        // Arrange
        $lenientParser = Modifiers::lenient(new StringParser);

        // Act
        $result = $lenientParser->parse(123);

        // Assert
        $this->assertNull($result);
    }

    public function test_describe_returns_lenient_wrapped_description(): void
    {
        // Arrange
        $lenientParser = Modifiers::lenient(new StringParser);

        // Act
        $description = $lenientParser->describe();

        // Assert
        $this->assertSame('lenient<string>', $description);
    }
}
