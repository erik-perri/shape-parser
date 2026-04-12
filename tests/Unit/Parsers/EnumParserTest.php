<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\EnumParser;
use Sourcetoad\ShapeParser\Tests\Fixtures\PriorityEnum;
use Sourcetoad\ShapeParser\Tests\Fixtures\StatusEnum;

#[CoversClass(EnumParser::class)]
class EnumParserTest extends TestCase
{
    public function test_parse_returns_backed_enum_case(): void
    {
        // Arrange
        $parser = new EnumParser(StatusEnum::class);
        $data = json_decode('"active"');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(StatusEnum::Active, $result);
    }

    public function test_parse_returns_unit_enum_case(): void
    {
        // Arrange
        $parser = new EnumParser(PriorityEnum::class);
        $data = json_decode('"High"');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(PriorityEnum::High, $result);
    }

    public function test_parse_throws_when_backed_value_unknown(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage(sprintf('Expected enum<%s>, got "unknown"', StatusEnum::class));

        // Arrange
        $parser = new EnumParser(StatusEnum::class);

        // Act
        $parser->parse('unknown');

        // Assert
        // No assertions, only expectations.
    }

    public function test_parse_throws_when_unit_case_unknown(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage(sprintf('Expected enum<%s>, got "Medium"', PriorityEnum::class));

        // Arrange
        $parser = new EnumParser(PriorityEnum::class);

        // Act
        $parser->parse('Medium');

        // Assert
        // No assertions, only expectations.
    }

    public function test_parse_throws_when_invalid_type(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage(sprintf('Expected enum<%s>, got bool', StatusEnum::class));

        // Arrange
        $parser = new EnumParser(StatusEnum::class);

        // Act
        $parser->parse(true);

        // Assert
        // No assertions, only expectations.
    }

    public function test_describe(): void
    {
        // Arrange
        $parser = new EnumParser(StatusEnum::class);

        // Act
        $description = $parser->describe();

        // Assert
        $this->assertSame(sprintf('enum<%s>', StatusEnum::class), $description);
    }
}
