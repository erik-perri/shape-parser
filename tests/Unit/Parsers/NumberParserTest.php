<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\NumberParser;

#[CoversClass(NumberParser::class)]
class NumberParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse_returns_result(string $json, int|float $expected): void
    {
        // Arrange
        $parser = new NumberParser;
        $data = json_decode($json);

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{json: string, expected: int|float}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'integer' => [
                'json' => '1',
                'expected' => 1,
            ],
            'float' => [
                'json' => '1.5',
                'expected' => 1.5,
            ],
        ];
    }

    public function test_parse_throws_when_given_string(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected number, got string');

        // Arrange
        $parser = new NumberParser;
        $data = json_decode('"1"');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}
