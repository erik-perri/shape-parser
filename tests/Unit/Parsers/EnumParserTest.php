<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Unit\Parsers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Exceptions\ParseException;
use Sourcetoad\ShapeParser\Parsers\EnumParser;
use Sourcetoad\ShapeParser\Tests\Fixtures\PriorityEnum;
use Sourcetoad\ShapeParser\Tests\Fixtures\StatusEnum;

#[CoversClass(EnumParser::class)]
class EnumParserTest extends TestCase
{
    #[DataProvider('parseCasesProvider')]
    public function test_parse_returns_enum_case(string $enumClass, mixed $input, mixed $expected): void
    {
        // Arrange
        $parser = new EnumParser($enumClass);

        // Act
        $result = $parser->parse($input);

        // Assert
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{enumClass: class-string, input: mixed, expected: mixed}>
     */
    public static function parseCasesProvider(): array
    {
        return [
            'backed enum case' => [
                'enumClass' => StatusEnum::class,
                'input' => json_decode('"active"'),
                'expected' => StatusEnum::Active,
            ],
            'unit enum case' => [
                'enumClass' => PriorityEnum::class,
                'input' => json_decode('"High"'),
                'expected' => PriorityEnum::High,
            ],
        ];
    }

    #[DataProvider('invalidCasesProvider')]
    public function test_parse_throws_when_invalid(string $enumClass, mixed $input, string $expectedMessage): void
    {
        // Expectations
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        // Arrange
        $parser = new EnumParser($enumClass);

        // Act
        $parser->parse($input);

        // Assert
        // No assertions, only expectations.
    }

    /**
     * @return array<string, array{enumClass: class-string, input: mixed, expectedMessage: string}>
     */
    public static function invalidCasesProvider(): array
    {
        return [
            'backed value unknown' => [
                'enumClass' => StatusEnum::class,
                'input' => 'unknown',
                'expectedMessage' => sprintf('Expected enum<%s>, got "unknown"', StatusEnum::class),
            ],
            'unit case unknown' => [
                'enumClass' => PriorityEnum::class,
                'input' => 'Medium',
                'expectedMessage' => sprintf('Expected enum<%s>, got "Medium"', PriorityEnum::class),
            ],
            'wrong type' => [
                'enumClass' => StatusEnum::class,
                'input' => true,
                'expectedMessage' => sprintf('Expected enum<%s>, got bool', StatusEnum::class),
            ],
        ];
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
