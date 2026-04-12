<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Tests\Feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Sourcetoad\ShapeParser\ShapeFactory;
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
        ];

        $factory = new ShapeFactory;
        $parser = $factory->list(
            $factory->discriminatedUnion('version', [
                $factory->object([
                    'version' => $factory->literal(1),
                    'id' => $factory->integer(),
                    'title' => $factory->string(),
                ])->transform(fn (array $a) => new ContentData($a['id'], $a['title'], null)),
                $factory->object([
                    'version' => $factory->literal(2),
                    'id' => $factory->integer(),
                    'title' => $factory->string(),
                    'url' => $factory->string(),
                ])->transform(fn (array $a) => new ContentData($a['id'], $a['title'], $a['url'])),
                $factory->object([
                    'version' => $factory->literal(3),
                    'id' => $factory->integer(),
                    'title' => $factory->string(),
                    'link' => $factory->string(),
                ])->transform(fn (array $a) => new ContentData($a['id'], $a['title'], $a['link'])),
            ]),
        )->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        $this->assertInstanceOf(ContentData::class, $result[0]);
        $this->assertSame(1, $result[0]->id);
        $this->assertSame('Sample title A', $result[0]->title);
        $this->assertNull($result[0]->url);

        $this->assertInstanceOf(ContentData::class, $result[1]);
        $this->assertSame(2, $result[1]->id);
        $this->assertSame('Sample title B', $result[1]->title);
        $this->assertSame('https://example.com', $result[1]->url);

        $this->assertInstanceOf(ContentData::class, $result[2]);
        $this->assertSame(3, $result[2]->id);
        $this->assertSame('Sample title C', $result[2]->title);
        $this->assertSame('https://example.com', $result[2]->url);
    }

    public function test_discriminated_union_hydrates_result_dto(): void
    {
        // Arrange
        $data = [
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
        ];

        $factory = new ShapeFactory;
        $parser = $factory->list(
            $factory->discriminatedUnion('version', [
                $factory->object([
                    'version' => $factory->literal(1),
                    'id' => $factory->integer(),
                    'title' => $factory->string(),
                ]),
                $factory->object([
                    'version' => $factory->literal(2),
                    'id' => $factory->integer(),
                    'title' => $factory->string(),
                    'url' => $factory->string(),
                ]),
                $factory->object([
                    'version' => $factory->literal(3),
                    'id' => $factory->integer(),
                    'title' => $factory->string(),
                    'link' => $factory->string(),
                ]),
            ])->transform(fn (array $parsed) => match ($parsed['version']) {
                1 => new ContentData($parsed['id'], $parsed['title'], null),
                2 => new ContentData($parsed['id'], $parsed['title'], $parsed['url']),
                3 => new ContentData($parsed['id'], $parsed['title'], $parsed['link']),
            }),
        )
            ->lenient();

        // Act
        $result = $parser->parse($data);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        $this->assertInstanceOf(ContentData::class, $result[0]);
        $this->assertSame(1, $result[0]->id);
        $this->assertSame('Sample title A', $result[0]->title);
        $this->assertNull($result[0]->url);

        $this->assertInstanceOf(ContentData::class, $result[1]);
        $this->assertSame(2, $result[1]->id);
        $this->assertSame('Sample title B', $result[1]->title);
        $this->assertSame('https://example.com', $result[1]->url);

        $this->assertInstanceOf(ContentData::class, $result[2]);
        $this->assertSame(3, $result[2]->id);
        $this->assertSame('Sample title C', $result[2]->title);
        $this->assertSame('https://example.com', $result[2]->url);
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

        $factory = new ShapeFactory;
        $parser = $factory->object([
            'title' => $factory->string(),
            'author' => $factory->object([
                'user_id' => $factory->integer(),
                'first_name' => $factory->string(),
                'created_at' => $factory->string(),
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
