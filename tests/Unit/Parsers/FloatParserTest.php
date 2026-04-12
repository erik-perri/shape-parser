<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\FloatParser;

#[CoversClass(FloatParser::class)]
class FloatParserTest extends TestCase
{
    public function testParseReturnsFloatResult(): void
    {
        // Arrange
        $parser = new FloatParser();
        $data = json_decode('1.5');

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertSame(1.5, $result);
    }

    #[DataProvider('invalidCasesProvider')]
    public function testParseThrowsWhenInvalid(string $json, string $expectedMessage): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        // Arrange
        $parser = new FloatParser();
        $data = json_decode($json);

        // Act
        $parser->parse($data);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{json: string, expectedMessage: string}>
     */
    public static function invalidCasesProvider(): array
    {
        return [
            'integer' => [
                'json' => '1',
                'expectedMessage' => 'Expected float, got int',
            ],
            'string' => [
                'json' => '"1.5"',
                'expectedMessage' => 'Expected float, got string',
            ],
        ];
    }
}
