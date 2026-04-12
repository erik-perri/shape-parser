<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\IntegerParser;
use Sourcetoad\ShapeParser\Parsers\LiteralParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\Parsers\StringParser;
use Sourcetoad\ShapeParser\Parsers\TransformParser;

#[CoversClass(TransformParser::class)]
class TransformParserTest extends TestCase
{
    public function testParseAppliesClosureToInnerResult(): void
    {
        // Arrange
        $parser = (new StringParser())->transform(fn(string $s) => strtoupper($s));

        // Act
        $result = $parser->parse('hello');

        // Assert
        $this->assertSame('HELLO', $result);
    }

    public function testParseCanProduceObjectFromPrimitive(): void
    {
        // Arrange
        $parser = (new StringParser())->transform(
            fn(string $s) => new DateTimeImmutable($s),
        );

        // Act
        $result = $parser->parse('2026-04-12');

        // Assert
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('2026-04-12', $result->format('Y-m-d'));
    }

    public function testParseHydratesDtoFromObjectShape(): void
    {
        // Arrange
        $parser = (new ObjectParser([
            'id' => new IntegerParser(),
            'title' => new StringParser(),
        ]))->transform(fn(array $a) => new TransformParserSampleDto($a['id'], $a['title']));

        // Act
        $result = $parser->parse(['id' => 1, 'title' => 'Foo']);

        // Assert
        $this->assertInstanceOf(TransformParserSampleDto::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame('Foo', $result->title);
    }

    public function testParseThrowsParseExceptionFromInnerParser(): void
    {
        // Expectations
        $this->expectException(ParseException::class);

        // Arrange
        $parser = (new StringParser())->transform(fn(string $s) => strtoupper($s));

        // Act
        $parser->parse(123);

        // Assert
        // No assertions, only expectations.
    }

    public function testParseDoesNotWrapClosureExceptions(): void
    {
        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('closure boom');

        // Arrange
        $parser = (new StringParser())->transform(function (string $s): string {
            throw new RuntimeException('closure boom');
        });

        // Act
        $parser->parse('hello');

        // Assert
        // No assertions, only expectations.
    }

    public function testChainedTransformComposesClosures(): void
    {
        // Arrange
        $parser = (new IntegerParser())
            ->transform(fn(int $i) => $i + 1)
            ->transform(fn(int $i) => $i * 10);

        // Act
        $result = $parser->parse(4);

        // Assert
        $this->assertSame(50, $result);
    }

    public function testLenientAfterTransformReturnsNullOnInnerFailure(): void
    {
        // Arrange
        $parser = (new ObjectParser([
            'id' => new IntegerParser(),
            'title' => new StringParser(),
        ]))
            ->transform(fn(array $a) => new TransformParserSampleDto($a['id'], $a['title']))
            ->lenient();

        // Act
        $bad = $parser->parse(['id' => 'not-an-int', 'title' => 'Foo']);
        $good = $parser->parse(['id' => 2, 'title' => 'Bar']);

        // Assert
        $this->assertNull($bad);
        $this->assertInstanceOf(TransformParserSampleDto::class, $good);
        $this->assertSame(2, $good->id);
    }

    public function testNullableAfterTransformReturnsNullOnNullInput(): void
    {
        // Arrange
        $parser = (new ObjectParser([
            'id' => new IntegerParser(),
            'title' => new StringParser(),
        ]))
            ->transform(fn(array $a) => new TransformParserSampleDto($a['id'], $a['title']))
            ->nullable();

        // Act
        $nullResult = $parser->parse(null);
        $dtoResult = $parser->parse(['id' => 3, 'title' => 'Baz']);

        // Assert
        $this->assertNull($nullResult);
        $this->assertInstanceOf(TransformParserSampleDto::class, $dtoResult);
    }

    public function testFallbackAfterLenientAfterTransformReturnsFallback(): void
    {
        // Arrange
        $fallback = new TransformParserSampleDto(0, 'fallback');
        $parser = (new ObjectParser([
            'id' => new IntegerParser(),
            'title' => new StringParser(),
        ]))
            ->transform(fn(array $a) => new TransformParserSampleDto($a['id'], $a['title']))
            ->lenient()
            ->fallback($fallback);

        // Act
        $result = $parser->parse(['id' => 'not-an-int', 'title' => 'Foo']);

        // Assert
        $this->assertSame($fallback, $result);
    }

    public function testDescribeChainsInnerDescription(): void
    {
        // Arrange
        $parser = (new StringParser())->transform(fn(string $s) => $s);

        // Act
        $description = $parser->describe();

        // Assert
        $this->assertSame('transform<string>', $description);
    }

    public function testTransformInsideDiscriminatedUnionVariant(): void
    {
        // Arrange
        $v1 = (new ObjectParser([
            'version' => new LiteralParser(1),
            'title' => new StringParser(),
        ]))->transform(fn(array $a) => new TransformParserSampleDto(1, $a['title']));

        $v2 = (new ObjectParser([
            'version' => new LiteralParser(2),
            'title' => new StringParser(),
        ]))->transform(fn(array $a) => new TransformParserSampleDto(2, $a['title']));

        $parser = new \Sourcetoad\ShapeParser\Parsers\DiscriminatedUnionParser(
            'version',
            [$v1, $v2],
        );

        // Act
        $first = $parser->parse(['version' => 1, 'title' => 'A']);
        $second = $parser->parse(['version' => 2, 'title' => 'B']);

        // Assert
        $this->assertInstanceOf(TransformParserSampleDto::class, $first);
        $this->assertSame(1, $first->id);
        $this->assertSame('A', $first->title);
        $this->assertInstanceOf(TransformParserSampleDto::class, $second);
        $this->assertSame(2, $second->id);
        $this->assertSame('B', $second->title);
    }
}

final readonly class TransformParserSampleDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
