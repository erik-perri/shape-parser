<?php

/** @noinspection PhpExpressionResultUnusedInspection */

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Type;

use Sourcetoad\ShapeParser\ShapeFactory;
use function PHPStan\Testing\assertType;

class ShapeFactoryTypeTest
{
    public function testInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $factory = new ShapeFactory();
        $parser = $factory->integer();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int', $result);
    }

    public function testString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $factory = new ShapeFactory();
        $parser = $factory->string();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string', $result);
    }

    public function testObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"stringValue": "foo", "integerValue": 123}');

        $factory = new ShapeFactory();
        $parser = $factory->object([
            'stringValue' => $factory->string(),
            'integerValue' => $factory->integer(),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{stringValue: string, integerValue: int}', $result);
    }

    public function testNestedObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"data": {"stringValue": "foo"}}');

        $factory = new ShapeFactory();
        $parser = $factory->object([
            'data' => $factory->object([
                'stringValue' => $factory->string(),
            ]),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{data: array{stringValue: string}}', $result);
    }

    public function testUnionShape(): void
    {
        // Arrange
        $factory = new ShapeFactory();

        if (random_int(0, 1) === 0) {
            $data = json_decode('{"data": {"stringValue": 123}}');
            $shape = [
                'data' => $factory->object(['stringValue' => $factory->string()]),
            ];
        } else {
            $data = json_decode('{"data": {"integerValue": 123}}');
            $shape = [
                'data' => $factory->object(['integerValue' => $factory->integer()]),
            ];
        }

        $parser = $factory->object($shape);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{data: array{integerValue: int}}|array{data: array{stringValue: string}}', $result);
    }

    public function testEmptyShape(): void
    {
        // Arrange
        $data = json_decode('{}');

        $factory = new ShapeFactory();
        $parser = $factory->object([]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{}', $result);
    }
}
