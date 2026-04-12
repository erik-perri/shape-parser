<?php

/** @noinspection PhpExpressionResultUnusedInspection */

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Type;

use DateTimeImmutable;
use Sourcetoad\ShapeParser\ShapeFactory;
use Sourcetoad\ShapeParser\Tests\Fixtures\ContentData;
use Sourcetoad\ShapeParser\Tests\Fixtures\SampleData;
use Sourcetoad\ShapeParser\Tests\Fixtures\StatusEnum;

use function PHPStan\Testing\assertType;

class ShapeFactoryTypeTest
{
    public function testDiscriminatedUnion(): void
    {
        // Arrange
        $data = json_decode('[{"type": "a", "foo": 1}, {"type": "b", "bar": "baz"}]');

        $factory = new ShapeFactory;
        $parser = $factory->discriminatedUnion(
            'type',
            [
                $factory->object(['type' => $factory->literal('a'), 'foo' => $factory->integer()]),
                $factory->object(['type' => $factory->literal('b'), 'bar' => $factory->string()]),
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

        $factory = new ShapeFactory;
        $parser = $factory->boolean();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('bool', $result);
    }

    public function testFloat(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $factory = new ShapeFactory;
        $parser = $factory->float();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float', $result);
    }

    public function testInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $factory = new ShapeFactory;
        $parser = $factory->integer();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int', $result);
    }

    public function testNumber(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $factory = new ShapeFactory;
        $parser = $factory->number();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|int', $result);
    }

    public function testString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $factory = new ShapeFactory;
        $parser = $factory->string();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string', $result);
    }

    public function testList(): void
    {
        // Arrange
        $data = json_decode('["foo", "bar"]');

        $factory = new ShapeFactory;
        $parser = $factory->list($factory->string());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<string>', $result);
    }

    public function testEnum(): void
    {
        // Arrange
        $data = json_decode('"active"');

        $factory = new ShapeFactory;
        $parser = $factory->enum(StatusEnum::class);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\StatusEnum', $result);
    }

    public function testDateTime(): void
    {
        // Arrange
        $data = json_decode('"2026-04-12T10:30:00Z"');

        $factory = new ShapeFactory;
        $parser = $factory->dateTime();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('DateTimeImmutable', $result);
    }

    public function testRecord(): void
    {
        // Arrange
        $data = json_decode('{"foo": 123, "bar": 456}');

        $factory = new ShapeFactory;
        $parser = $factory->record($factory->string(), $factory->integer());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array<string, int>', $result);
    }

    public function testUnion(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $factory = new ShapeFactory;
        $parser = $factory->union($factory->string(), $factory->integer());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int|string', $result);
    }

    public function testObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"stringValue": "foo", "integerValue": 123}');

        $factory = new ShapeFactory;
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
        $data = json_decode('{"data": {"stringValue": "foo", "listValue":  ["foo", "bar"]}}');

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'data' => $factory->object([
                'stringValue' => $factory->string(),
                'listValue' => $factory->list($factory->string()),
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
        $factory = new ShapeFactory;

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

        $factory = new ShapeFactory;
        $parser = $factory->object([]);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{}', $result);
    }

    public function testLenientFloat(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $factory = new ShapeFactory;
        $parser = $factory->float()->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|null', $result);
    }

    public function testLenientNumber(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $factory = new ShapeFactory;
        $parser = $factory->number()->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|int|null', $result);
    }

    public function testLenientBoolean(): void
    {
        // Arrange
        $data = json_decode('true');

        $factory = new ShapeFactory;
        $parser = $factory->boolean()->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('bool|null', $result);
    }

    public function testLenientString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $factory = new ShapeFactory;
        $parser = $factory->string()->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string|null', $result);
    }

    public function testLenientInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $factory = new ShapeFactory;
        $parser = $factory->integer()->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int|null', $result);
    }

    public function testLenientList(): void
    {
        // Arrange
        $data = json_decode('["foo", "bar"]');

        $factory = new ShapeFactory;
        $parser = $factory->list($factory->string())->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<string>', $result);
    }

    public function testLenientRecord(): void
    {
        // Arrange
        $data = json_decode('{"foo": 123, "bar": 456}');

        $factory = new ShapeFactory;
        $parser = $factory->record($factory->string(), $factory->integer())->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array<string, int>', $result);
    }

    public function testLenientObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"stringValue": "foo", "integerValue": 123}');

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'stringValue' => $factory->string(),
            'integerValue' => $factory->integer(),
        ])->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{stringValue: string, integerValue: int}|null', $result);
    }

    public function testFallbackString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $factory = new ShapeFactory;
        $parser = $factory->string()->lenient()->fallback('fallback');

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string', $result);
    }

    public function testFallbackInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $factory = new ShapeFactory;
        $parser = $factory->integer()->lenient()->fallback(0);

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int', $result);
    }

    public function testFallbackInObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"name": "foo", "count": 123}');

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'name' => $factory->string()->lenient()->fallback('unknown'),
            'count' => $factory->integer()->lenient()->fallback(0),
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

        $factory = new ShapeFactory;
        $parser = $factory->list($factory->string()->lenient());

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<string|null>', $result);
    }

    public function testNullableString(): void
    {
        // Arrange
        $data = json_decode('"foo"');

        $factory = new ShapeFactory;
        $parser = $factory->string()->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('string|null', $result);
    }

    public function testNullableInteger(): void
    {
        // Arrange
        $data = json_decode('123');

        $factory = new ShapeFactory;
        $parser = $factory->integer()->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('int|null', $result);
    }

    public function testNullableBoolean(): void
    {
        // Arrange
        $data = json_decode('true');

        $factory = new ShapeFactory;
        $parser = $factory->boolean()->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('bool|null', $result);
    }

    public function testNullableFloat(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $factory = new ShapeFactory;
        $parser = $factory->float()->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|null', $result);
    }

    public function testNullableNumber(): void
    {
        // Arrange
        $data = json_decode('1.5');

        $factory = new ShapeFactory;
        $parser = $factory->number()->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('float|int|null', $result);
    }

    public function testNullableList(): void
    {
        // Arrange
        $data = json_decode('["foo", "bar"]');

        $factory = new ShapeFactory;
        $parser = $factory->list($factory->string())->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('list<string>|null', $result);
    }

    public function testNullableRecord(): void
    {
        // Arrange
        $data = json_decode('{"foo": 123, "bar": 456}');

        $factory = new ShapeFactory;
        $parser = $factory->record($factory->string(), $factory->integer())->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array<string, int>|null', $result);
    }

    public function testNullableObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"stringValue": "foo", "integerValue": 123}');

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'stringValue' => $factory->string(),
            'integerValue' => $factory->integer(),
        ])->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('array{stringValue: string, integerValue: int}|null', $result);
    }

    public function testNullableInObjectShape(): void
    {
        // Arrange
        $data = json_decode('{"name": "foo", "count": 123}');

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'name' => $factory->string()->nullable(),
            'count' => $factory->integer(),
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

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'name' => $factory->string(),
            'count' => $factory->integer()->optional(),
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

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'name' => $factory->string(),
            'count' => $factory->integer()->nullable()->optional(),
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

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'name' => $factory->string(),
            'address' => $factory->object([
                'street' => $factory->string(),
            ])->optional(),
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

        $factory = new ShapeFactory;
        $parser = $factory->string()->transform(fn (string $s): DateTimeImmutable => new DateTimeImmutable($s));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('DateTimeImmutable', $result);
    }

    public function testTransformObjectToDto(): void
    {
        // Arrange
        $data = json_decode('{"id": 1, "title": "Foo"}');

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'id' => $factory->integer(),
            'title' => $factory->string(),
        ])->transform(fn (array $a): SampleData => new SampleData($a['id'], $a['title']));

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\SampleData', $result);
    }

    public function testTransformLenient(): void
    {
        // Arrange
        $data = json_decode('{"id": 1, "title": "Foo"}');

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'id' => $factory->integer(),
            'title' => $factory->string(),
        ])
            ->transform(fn (array $a): SampleData => new SampleData($a['id'], $a['title']))
            ->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\SampleData|null', $result);
    }

    public function testTransformNullable(): void
    {
        // Arrange
        $data = json_decode('{"id": 1, "title": "Foo"}');

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'id' => $factory->integer(),
            'title' => $factory->string(),
        ])
            ->transform(fn (array $a): SampleData => new SampleData($a['id'], $a['title']))
            ->nullable();

        // Act
        $result = $parser->parse($data);

        // Assert
        assertType('Sourcetoad\ShapeParser\Tests\Fixtures\SampleData|null', $result);
    }

    public function testTransformListOfHydrated(): void
    {
        // Arrange
        $data = json_decode('[{"id": 1, "title": "Foo"}, {"id": 2, "title": "Bar"}]');

        $factory = new ShapeFactory;
        $parser = $factory->list(
            $factory->object([
                'id' => $factory->integer(),
                'title' => $factory->string(),
            ])->transform(fn (array $a): SampleData => new SampleData($a['id'], $a['title'])),
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

        $factory = new ShapeFactory;
        $parser = $factory->discriminatedUnion('version', [
            $factory->object([
                'version' => $factory->literal(1),
                'title' => $factory->string(),
            ])->transform(fn (array $a): SampleData => new SampleData(1, $a['title'])),
            $factory->object([
                'version' => $factory->literal(2),
                'title' => $factory->string(),
            ])->transform(fn (array $a): ContentData => new ContentData(2, $a['title'], null)),
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

        $factory = new ShapeFactory;
        $parser = $factory->list(
            $factory->discriminatedUnion('version', [
                $factory->object([
                    'version' => $factory->literal(1),
                    'title' => $factory->string(),
                ])->transform(fn (array $a): SampleData => new SampleData(1, $a['title'])),
                $factory->object([
                    'version' => $factory->literal(2),
                    'title' => $factory->string(),
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
