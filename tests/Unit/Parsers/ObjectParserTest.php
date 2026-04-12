<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;

#[CoversClass(ObjectParser::class)]
class ObjectParserTest extends TestCase
{
    public function test_parse_returns_result(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser,
            'bar' => new ObjectParser([
                'baz' => new IntegerParser,
            ]),
        ]);
        $data = json_decode('{"foo": "bar", "bar": {"baz": 123}}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['foo' => 'bar', 'bar' => ['baz' => 123]], $result);
    }

    public function test_parse_throws_when_invalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser,
        ]);
        $data = json_decode('{"bar": "foo"}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function test_optional_field_omitted_when_absent(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser,
            'bar' => (new StringParser)->optional(),
        ]);
        $data = json_decode('{"foo": "hello"}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['foo' => 'hello'], $result);
        $this->assertArrayNotHasKey('bar', $result);
    }

    public function test_optional_field_included_when_present(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser,
            'bar' => (new StringParser)->optional(),
        ]);
        $data = json_decode('{"foo": "hello", "bar": "world"}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['foo' => 'hello', 'bar' => 'world'], $result);
    }

    public function test_optional_field_throws_when_present_but_invalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new ObjectParser([
            'foo' => (new StringParser)->optional(),
        ]);
        $data = json_decode('{"foo": 123}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function test_optional_field_throws_when_present_null_on_non_nullable_inner(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new ObjectParser([
            'foo' => (new StringParser)->optional(),
        ]);
        $data = json_decode('{"foo": null}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function test_nullable_optional_field_accepts_absent_null_and_value(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => (new StringParser)->nullable()->optional(),
        ]);

        // Act + Assert
        $this->assertSame([], $parser->parse(json_decode('{}')));
        $this->assertSame(['foo' => null], $parser->parse(json_decode('{"foo": null}')));
        $this->assertSame(['foo' => 'hi'], $parser->parse(json_decode('{"foo": "hi"}')));
    }

    public function test_required_field_still_fails_when_optional_fields_also_absent(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser,
            'bar' => (new StringParser)->optional(),
        ]);
        $data = json_decode('{}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}
