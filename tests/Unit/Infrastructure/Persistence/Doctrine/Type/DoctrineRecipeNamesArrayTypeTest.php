<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Infrastructure\Persistence\Doctrine\Type\DoctrineRecipeNamesArrayType;
use PHPUnit\Framework\TestCase;

final class DoctrineRecipeNamesArrayTypeTest extends TestCase
{
    /**
     * @param array<RecipeName> $recipes
     * @dataProvider provideConvertToDatabaseValueData
     */
    public function testConvertToDatabaseValue(?array $recipes, ?string $expected): void
    {
        $platform = new PostgreSQL100Platform();
        $type = new DoctrineRecipeNamesArrayType();

        $actual = $type->convertToDatabaseValue($recipes, $platform);

        self::assertEquals($expected, $actual);
    }


    /**
     * @return \Generator<array<mixed>>
     */
    public function provideConvertToDatabaseValueData(): \Generator
    {
        yield [null, null];

        yield [[], '{}'];

        yield [
            [
                RecipeName::TYPED_PROPERTIES()
            ],
            '{"typed-properties"}',
        ];

        yield [
            [
                RecipeName::TYPED_PROPERTIES(),
                RecipeName::UNUSED_PRIVATE_METHODS(),
            ],
            '{"typed-properties","unused-private-methods"}',
        ];
    }

    /**
     * @param array<RecipeName> $expected
     * @dataProvider provideConvertToPHPValueData
     */
    public function testConvertToPHPValue(?string $value, ?array $expected): void
    {
        $platform = new PostgreSQL100Platform();
        $type = new DoctrineRecipeNamesArrayType();

        $actual = $type->convertToPHPValue($value, $platform);

        // Non-strict compare
        self::assertEquals($expected, $actual);
    }


    /**
     * @return \Generator<array<mixed>>
     */
    public function provideConvertToPHPValueData(): \Generator
    {
        yield [null, null];

        yield ['{}', []];

        yield [
            '{typed-properties}',
            [
                RecipeName::TYPED_PROPERTIES()
            ],
        ];

        yield [
            '{"typed-properties"}',
            [
                RecipeName::TYPED_PROPERTIES()
            ],
        ];

        yield [
            '{typed-properties,unused-private-methods}',
            [
                RecipeName::TYPED_PROPERTIES(),
                RecipeName::UNUSED_PRIVATE_METHODS(),
            ],
        ];

        yield [
            '{"typed-properties","unused-private-methods"}',
            [
                RecipeName::TYPED_PROPERTIES(),
                RecipeName::UNUSED_PRIVATE_METHODS(),
            ],
        ];
    }
}
