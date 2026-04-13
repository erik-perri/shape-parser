<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\Shape;
use Sourcetoad\ShapeParser\Tests\Fixtures\ContentData;
use Sourcetoad\ShapeParser\Tests\Fixtures\PostData;
use Sourcetoad\ShapeParser\Tests\Fixtures\UserData;

#[CoversNothing]
class HydratedShapeTest extends TestCase
{
    public function test_discriminated_union_hydrates_per_variant_dto(): void
    {
        // Arrange
        $data = [
            [
                [
                    'id' => 1,
                    'title' => 'Sample title A',
                    'version' => 1,
                ],
                [
                    'id' => 2,
                    'title' => 'Sample title B',
                    'url' => 'https://example.com',
                    'version' => 2,
                ],
                [
                    'id' => 3,
                    'title' => 'Sample title C',
                    'link' => 'https://example.com',
                    'version' => 3,
                ],
                [
                    'id' => 4,
                    'title' => 'Sample title D',
                    'link' => [
                        'url' => 'https://example.com',
                    ],
                    'version' => 4,
                ],
            ],
        ];

        $parser = Shape::tuple(
            Shape::list(
                Shape::discriminatedUnion('version', [
                    Shape::object([
                        'version' => Shape::literal(1),
                        'id' => Shape::integer(),
                        'title' => Shape::string(),
                    ])->transform(fn (array $a) => new ContentData($a['id'], $a['title'], null)),
                    Shape::object([
                        'version' => Shape::literal(2),
                        'id' => Shape::integer(),
                        'title' => Shape::string(),
                        'url' => Shape::string(),
                    ])->transform(fn (array $a) => new ContentData($a['id'], $a['title'], $a['url'])),
                    Shape::object([
                        'version' => Shape::literal(3),
                        'id' => Shape::integer(),
                        'title' => Shape::string(),
                        'link' => Shape::string(),
                    ])->transform(fn (array $a) => new ContentData($a['id'], $a['title'], $a['link'])),
                ]),
            )->lenient(),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertCount(1, $result);
        $this->assertCount(3, $result[0]);

        $this->assertInstanceOf(ContentData::class, $result[0][0]);
        $this->assertSame(1, $result[0][0]->id);
        $this->assertSame('Sample title A', $result[0][0]->title);
        $this->assertNull($result[0][0]->url);

        $this->assertInstanceOf(ContentData::class, $result[0][1]);
        $this->assertSame(2, $result[0][1]->id);
        $this->assertSame('Sample title B', $result[0][1]->title);
        $this->assertSame('https://example.com', $result[0][1]->url);

        $this->assertInstanceOf(ContentData::class, $result[0][2]);
        $this->assertSame(3, $result[0][2]->id);
        $this->assertSame('Sample title C', $result[0][2]->title);
        $this->assertSame('https://example.com', $result[0][2]->url);
    }

    public function test_discriminated_union_hydrates_result_dto(): void
    {
        // Arrange
        $data = [
            [
                [
                    'id' => 1,
                    'title' => 'Sample title A',
                    'version' => 1,
                ],
                [
                    'id' => 2,
                    'title' => 'Sample title B',
                    'url' => 'https://example.com',
                    'version' => 2,
                ],
                [
                    'id' => 3,
                    'title' => 'Sample title C',
                    'link' => 'https://example.com',
                    'version' => 3,
                ],
                [
                    'id' => 4,
                    'title' => 'Sample title D',
                    'link' => [
                        'url' => 'https://example.com',
                    ],
                    'version' => 4,
                ],
            ],
        ];

        $parser = Shape::tuple(
            Shape::list(
                Shape::discriminatedUnion('version', [
                    Shape::object([
                        'version' => Shape::literal(1),
                        'id' => Shape::integer(),
                        'title' => Shape::string(),
                    ]),
                    Shape::object([
                        'version' => Shape::literal(2),
                        'id' => Shape::integer(),
                        'title' => Shape::string(),
                        'url' => Shape::string(),
                    ]),
                    Shape::object([
                        'version' => Shape::literal(3),
                        'id' => Shape::integer(),
                        'title' => Shape::string(),
                        'link' => Shape::string(),
                    ]),
                ])->transform(fn (array $parsed) => match ($parsed['version']) {
                    1 => new ContentData($parsed['id'], $parsed['title'], null),
                    2 => new ContentData($parsed['id'], $parsed['title'], $parsed['url']),
                    3 => new ContentData($parsed['id'], $parsed['title'], $parsed['link']),
                }),
            )->lenient(),
        );

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertCount(1, $result);
        $this->assertCount(3, $result[0]);

        $this->assertInstanceOf(ContentData::class, $result[0][0]);
        $this->assertSame(1, $result[0][0]->id);
        $this->assertSame('Sample title A', $result[0][0]->title);
        $this->assertNull($result[0][0]->url);

        $this->assertInstanceOf(ContentData::class, $result[0][1]);
        $this->assertSame(2, $result[0][1]->id);
        $this->assertSame('Sample title B', $result[0][1]->title);
        $this->assertSame('https://example.com', $result[0][1]->url);

        $this->assertInstanceOf(ContentData::class, $result[0][2]);
        $this->assertSame(3, $result[0][2]->id);
        $this->assertSame('Sample title C', $result[0][2]->title);
        $this->assertSame('https://example.com', $result[0][2]->url);
    }

    public function test_nested_dto_hydration_is_bottom_up(): void
    {
        // Arrange
        $data = [
            'title' => 'Hello',
            'author' => [
                'user_id' => 7,
                'first_name' => 'User',
                'created_at' => '2026-04-12',
            ],
        ];

        $parser = Shape::object([
            'title' => Shape::string(),
            'author' => Shape::object([
                'user_id' => Shape::integer(),
                'first_name' => Shape::string(),
                'created_at' => Shape::string(),
            ])->transform(fn (array $a) => new UserData(
                userId: $a['user_id'],
                firstName: $a['first_name'],
                createdAt: $a['created_at'],
            )),
        ])->transform(fn (array $a) => new PostData(
            title: $a['title'],
            author: $a['author'],
        ));

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertInstanceOf(PostData::class, $result);
        $this->assertSame('Hello', $result->title);
        $this->assertInstanceOf(UserData::class, $result->author);
        $this->assertSame(7, $result->author->userId);
        $this->assertSame('User', $result->author->firstName);
    }
}
