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
    public function testParseReturnsResult(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser(),
            'bar' => new ObjectParser([
                'baz' => new IntegerParser(),
            ]),
        ]);
        $data = json_decode('{"foo": "bar", "bar": {"baz": 123}}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['foo' => 'bar', 'bar' => ['baz' => 123]], $result);
    }

    public function testParseThrowsWhenInvalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser(),
        ]);
        $data = json_decode('{"bar": "foo"}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function testOptionalFieldOmittedWhenAbsent(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser(),
            'bar' => (new StringParser())->optional(),
        ]);
        $data = json_decode('{"foo": "hello"}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['foo' => 'hello'], $result);
        $this->assertArrayNotHasKey('bar', $result);
    }

    public function testOptionalFieldIncludedWhenPresent(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser(),
            'bar' => (new StringParser())->optional(),
        ]);
        $data = json_decode('{"foo": "hello", "bar": "world"}');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(['foo' => 'hello', 'bar' => 'world'], $result);
    }

    public function testOptionalFieldThrowsWhenPresentButInvalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new ObjectParser([
            'foo' => (new StringParser())->optional(),
        ]);
        $data = json_decode('{"foo": 123}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function testOptionalFieldThrowsWhenPresentNullOnNonNullableInner(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new ObjectParser([
            'foo' => (new StringParser())->optional(),
        ]);
        $data = json_decode('{"foo": null}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    public function testNullableOptionalFieldAcceptsAbsentNullAndValue(): void
    {
        // Arrange
        $parser = new ObjectParser([
            'foo' => (new StringParser())->nullable()->optional(),
        ]);

        // Act + Assert
        $this->assertSame([], $parser->parse(json_decode('{}')));
        $this->assertSame(['foo' => null], $parser->parse(json_decode('{"foo": null}')));
        $this->assertSame(['foo' => 'hi'], $parser->parse(json_decode('{"foo": "hi"}')));
    }

    public function testRequiredFieldStillFailsWhenOptionalFieldsAlsoAbsent(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Failed to parse object');

        // Arrange
        $parser = new ObjectParser([
            'foo' => new StringParser(),
            'bar' => (new StringParser())->optional(),
        ]);
        $data = json_decode('{}');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}
