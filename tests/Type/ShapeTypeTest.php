<?php

/** @noinspection PhpExpressionResultUnusedInspection */

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Type;

use DateTimeImmutable;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\Shape;
use Sourcetoad\ShapeParser\Tests\Fixtures\ContentData;
use Sourcetoad\ShapeParser\Tests\Fixtures\SampleData;
use Sourcetoad\ShapeParser\Tests\Fixtures\StatusEnum;

use function PHPStan\Testing\assertType;

class ShapeTypeTest
{
    public function testDiscriminatedUnion(): void
    {
        // Arrange
        $data = json_decode('[{"type": "a", "foo": 1}, {"type": "b", "bar": "baz"}]');

        $parser = Shape::discriminatedUnion(
            'type',
            [
                Shape::object(['type' => Shape::literal('a'), 'foo' => Shape::integer()]),
                Shape::object(['type' => Shape::literal('b'), 'bar' => Shape::string()]),
            ],
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType("array{type: 'a', foo: int}|array{type: 'b', bar: string}", $result);
    }

    public function testBoolean(): void
    {
        // Arrange
        $data = json_decode('true');

        $parser = Shape::boolean();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('bool', $result);
    }

    public function testFloat(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $parser = Shape::float();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float', $result);
    }

    public function testInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $parser = Shape::integer();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int', $result);
    }

    public function testNumber(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $parser = Shape::number();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|int', $result);
    }

    public function testString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $parser = Shape::string();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string', $result);
    }

    public function testList(): void
    {
        // Arrange
        $data = json_decode('["foo", "bar"]');

        $parser = Shape::list(Shape::string());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<string>', $result);
    }

    public function testTuple(): void
    {
        // Arrange
        $data = json_decode('["foo", 42]');

        $parser = Shape::tuple(Shape::string(), Shape::integer());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{string, int}', $result);
    }

    public function testTupleOfThree(): void
    {
        // Arrange
        $data = json_decode('["foo", 42, true]');

        $parser = Shape::tuple(Shape::string(), Shape::integer(), Shape::boolean());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{string, int, bool}', $result);
    }

    public function testNullableTuple(): void
    {
        // Arrange
        $data = json_decode('["foo", 42]');

        $parser = Modifiers::nullable(Shape::tuple(Shape::string(), Shape::integer()));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{string, int}|null', $result);
    }

    public function testLenientTuple(): void
    {
        // Arrange
        $data = json_decode('["foo", 42]');

        $parser = Modifiers::lenient(Shape::tuple(Shape::string(), Shape::integer()));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{string, int}|null', $result);
    }

    public function testTransformTupleToDto(): void
    {
        // Arrange
        $data = json_decode('["Foo", 1]');

        $parser = Modifiers::transform(
            Shape::tuple(Shape::string(), Shape::integer()),
            fn (array $t): SampleData => new SampleData($t[1], $t[0]),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\SampleData', $result);
    }

    public function testTupleInsideObject(): void
    {
        // Arrange
        $data = json_decode('{"label": "x", "coord": [1.0, 2.0]}');

        $parser = Shape::object([
            'label' => Shape::string(),
            'coord' => Shape::tuple(Shape::float(), Shape::float()),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{label: string, coord: array{float, float}}', $result);
    }

    public function testEnum(): void
    {
        // Arrange
        $data = json_decode('"active"');

        $parser = Shape::enum(StatusEnum::class);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\StatusEnum', $result);
    }

    public function testDateTime(): void
    {
        // Arrange
        $data = json_decode('"2026-04-12T10:30:00Z"');

        $parser = Shape::dateTime();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('DateTimeImmutable', $result);
    }

    public function testRecord(): void
    {
        // Arrange
        $data = json_decode('{"foo": 123, "bar": 456}');

        $parser = Shape::record(Shape::string(), Shape::integer());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array<string, int>', $result);
    }

    public function testUnion(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $parser = Shape::union(Shape::string(), Shape::integer());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int|string', $result);
    }

    public function testObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"stringValue": "foo", "integerValue": 123}');

        $parser = Shape::object([
            'stringValue' => Shape::string(),
            'integerValue' => Shape::integer(),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{stringValue: string, integerValue: int}', $result);
    }

    public function testNestedObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"data": {"stringValue": "foo", "listValue":  ["foo", "bar"]}}');

        $parser = Shape::object([
            'data' => Shape::object([
                'stringValue' => Shape::string(),
                'listValue' => Shape::list(Shape::string()),
            ]),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{data: array{stringValue: string, listValue: list<string>}}', $result);
    }

    public function testUnionShape(): void
    {
        // Arrange
        if (random_int(0, 1) === 0) {
            $data = json_decode('{"data": {"stringValue": 123}}');
            $shape = [
                'data' => Shape::object(['stringValue' => Shape::string()]),
            ];
        } else {
            $data = json_decode('{"data": {"integerValue": 123}}');
            $shape = [
                'data' => Shape::object(['integerValue' => Shape::integer()]),
            ];
        }

        $parser = Shape::object($shape);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{data: array{integerValue: int}}|array{data: array{stringValue: string}}', $result);
    }

    public function testEmptyShape(): void
    {
        // Arrange
        $data = json_decode('{}');

        $parser = Shape::object([]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{}', $result);
    }

    public function testLenientFloat(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $parser = Modifiers::lenient(Shape::float());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|null', $result);
    }

    public function testLenientNumber(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $parser = Modifiers::lenient(Shape::number());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|int|null', $result);
    }

    public function testLenientBoolean(): void
    {
        // Arrange
        $data = json_decode('true');

        $parser = Modifiers::lenient(Shape::boolean());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('bool|null', $result);
    }

    public function testLenientString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $parser = Modifiers::lenient(Shape::string());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string|null', $result);
    }

    public function testLenientInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $parser = Modifiers::lenient(Shape::integer());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int|null', $result);
    }

    public function testLenientList(): void
    {
        // Arrange
        $data = json_decode('["foo", "bar"]');

        $parser = Modifiers::lenient(Shape::list(Shape::string()));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<string>', $result);
    }

    public function testLenientRecord(): void
    {
        // Arrange
        $data = json_decode('{"foo": 123, "bar": 456}');

        $parser = Modifiers::lenient(Shape::record(Shape::string(), Shape::integer()));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array<string, int>', $result);
    }

    public function testLenientObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"stringValue": "foo", "integerValue": 123}');

        $parser = Modifiers::lenient(Shape::object([
            'stringValue' => Shape::string(),
            'integerValue' => Shape::integer(),
        ]));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{stringValue: string, integerValue: int}|null', $result);
    }

    public function testFallbackString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $parser = Modifiers::fallback(Shape::string(), 'fallback');

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string', $result);
    }

    public function testFallbackInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $parser = Modifiers::fallback(Shape::integer(), 0);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int', $result);
    }

    public function testFallbackInObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"name": "foo", "count": 123}');

        $parser = Shape::object([
            'name' => Modifiers::fallback(Shape::string(), 'unknown'),
            'count' => Modifiers::fallback(Shape::integer(), 0),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{name: string, count: int}', $result);
    }

    public function testLenientItemsInList(): void
    {
        // Arrange
        $data = json_decode('["foo", "bar"]');

        $parser = Shape::list(Modifiers::lenient(Shape::string()));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<string|null>', $result);
    }

    public function testNullableString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $parser = Modifiers::nullable(Shape::string());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string|null', $result);
    }

    public function testNullableInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $parser = Modifiers::nullable(Shape::integer());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int|null', $result);
    }

    public function testNullableBoolean(): void
    {
        // Arrange
        $data = json_decode('true');

        $parser = Modifiers::nullable(Shape::boolean());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('bool|null', $result);
    }

    public function testNullableFloat(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $parser = Modifiers::nullable(Shape::float());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|null', $result);
    }

    public function testNullableNumber(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $parser = Modifiers::nullable(Shape::number());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|int|null', $result);
    }

    public function testNullableList(): void
    {
        // Arrange
        $data = json_decode('["foo", "bar"]');

        $parser = Modifiers::nullable(Shape::list(Shape::string()));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<string>|null', $result);
    }

    public function testNullableRecord(): void
    {
        // Arrange
        $data = json_decode('{"foo": 123, "bar": 456}');

        $parser = Modifiers::nullable(Shape::record(Shape::string(), Shape::integer()));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array<string, int>|null', $result);
    }

    public function testNullableObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"stringValue": "foo", "integerValue": 123}');

        $parser = Modifiers::nullable(Shape::object([
            'stringValue' => Shape::string(),
            'integerValue' => Shape::integer(),
        ]));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{stringValue: string, integerValue: int}|null', $result);
    }

    public function testNullableInObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"name": "foo", "count": 123}');

        $parser = Shape::object([
            'name' => Modifiers::nullable(Shape::string()),
            'count' => Shape::integer(),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{name: string|null, count: int}', $result);
    }

    public function testOptionalInObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"name": "foo"}');

        $parser = Shape::object([
            'name' => Shape::string(),
            'count' => Modifiers::optional(Shape::integer()),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{name: string, count?: int}', $result);
    }

    public function testNullableOptionalInObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"name": "foo", "count": null}');

        $parser = Shape::object([
            'name' => Shape::string(),
            'count' => Modifiers::optional(Modifiers::nullable(Shape::integer())),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{name: string, count?: int|null}', $result);
    }

    public function testOptionalObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"name": "foo"}');

        $parser = Shape::object([
            'name' => Shape::string(),
            'address' => Modifiers::optional(Shape::object([
                'street' => Shape::string(),
            ])),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{name: string, address?: array{street: string}}', $result);
    }

    public function testTransformString(): void
    {
        // Arrange
        $data = json_decode('"2026-04-12"');

        $parser = Modifiers::transform(
            Shape::string(),
            fn (string $s): DateTimeImmutable => new DateTimeImmutable($s),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('DateTimeImmutable', $result);
    }

    public function testTransformObjectToDto(): void
    {
        // Arrange
        $data = json_decode('{"id": 1, "title": "Foo"}');

        $parser = Modifiers::transform(
            Shape::object([
                'id' => Shape::integer(),
                'title' => Shape::string(),
            ]),
            fn (array $a): SampleData => new SampleData($a['id'], $a['title']),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\SampleData', $result);
    }

    public function testTransformLenient(): void
    {
        // Arrange
        $data = json_decode('{"id": 1, "title": "Foo"}');

        $parser = Modifiers::lenient(
            Modifiers::transform(
                Shape::object([
                    'id' => Shape::integer(),
                    'title' => Shape::string(),
                ]),
                fn (array $a): SampleData => new SampleData($a['id'], $a['title']),
            ),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\SampleData|null', $result);
    }

    public function testTransformNullable(): void
    {
        // Arrange
        $data = json_decode('{"id": 1, "title": "Foo"}');

        $parser = Modifiers::nullable(
            Modifiers::transform(
                Shape::object([
                    'id' => Shape::integer(),
                    'title' => Shape::string(),
                ]),
                fn (array $a): SampleData => new SampleData($a['id'], $a['title']),
            ),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\SampleData|null', $result);
    }

    public function testTransformListOfHydrated(): void
    {
        // Arrange
        $data = json_decode('[{"id": 1, "title": "Foo"}, {"id": 2, "title": "Bar"}]');

        $parser = Shape::list(
            Modifiers::transform(
                Shape::object([
                    'id' => Shape::integer(),
                    'title' => Shape::string(),
                ]),
                fn (array $a): SampleData => new SampleData($a['id'], $a['title']),
            ),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<Sourcetoad\ShapeParser\Tests\Fixtures\SampleData>', $result);
    }

    public function testTransformDiscriminatedUnion(): void
    {
        // Arrange
        $data = json_decode('{"version": 1, "title": "Foo"}');

        $parser = Shape::discriminatedUnion('version', [
            Modifiers::transform(
                Shape::object([
                    'version' => Shape::literal(1),
                    'title' => Shape::string(),
                ]),
                fn (array $a): SampleData => new SampleData(1, $a['title']),
            ),
            Modifiers::transform(
                Shape::object([
                    'version' => Shape::literal(2),
                    'title' => Shape::string(),
                ]),
                fn (array $a): ContentData => new ContentData(2, $a['title'], null),
            ),
        ]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType(
            'Sourcetoad\ShapeParser\Tests\Fixtures\ContentData|Sourcetoad\ShapeParser\Tests\Fixtures\SampleData',
            $result,
        );
    }

    public function testTransformDiscriminatedUnionMixed(): void
    {
        // Arrange
        $data = json_decode('[{"version": 1, "title": "Foo"}, {"version": 2, "title": "Bar"}]');

        $parser = Shape::list(
            Shape::discriminatedUnion('version', [
                Modifiers::transform(
                    Shape::object([
                        'version' => Shape::literal(1),
                        'title' => Shape::string(),
                    ]),
                    fn (array $a): SampleData => new SampleData(1, $a['title']),
                ),
                Shape::object([
                    'version' => Shape::literal(2),
                    'title' => Shape::string(),
                ]),
            ]),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType(
            "list<array{version: 2, title: string}|Sourcetoad\ShapeParser\Tests\Fixtures\SampleData>",
            $result,
        );
    }
}
