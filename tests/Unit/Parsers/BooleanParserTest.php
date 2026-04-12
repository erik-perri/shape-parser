<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\BooleanParser;

#[CoversClass(BooleanParser::class)]
class BooleanParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function testParseReturnsResult(string $json, bool $expected): void
    {
        // Arrange
        $parser = new BooleanParser();
        $data = json_decode($json);

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{json: string, expected: bool}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'true' => [
                'json' => 'true',
                'expected' => true,
            ],
            'false' => [
                'json' => 'false',
                'expected' => false,
            ],
        ];
    }

    public function testParseThrowsWhenInvalid(): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Expected bool, got int');

        // Arrange
        $parser = new BooleanParser();
        $data = json_decode('1');

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }
}
