<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\RecipeBaseline;
use PHPMate\Infrastructure\Persistence\Doctrine\Type\DoctrineRecipeBaselinesArrayType;
use PHPUnit\Framework\TestCase;

final class DoctrineRecipeBaselinesArrayTypeTest extends TestCase
{
    /**
     * @param array<RecipeBaseline> $baselines
     * @dataProvider provideConvertToDatabaseValueData
     */
    public function testConvertToDatabaseValue(?array $baselines, ?string $expected): void
    {
        $platform = new PostgreSQL100Platform();
        $type = new DoctrineRecipeBaselinesArrayType();

        $actual = $type->convertToDatabaseValue($baselines, $platform);

        self::assertEquals($expected, $actual);
    }

    /**
     * @return \Generator<array<mixed>>
     */
    public function provideConvertToDatabaseValueData(): \Generator
    {
        yield [null, null];

        yield [[], '[]'];

        yield [
                [
                    new RecipeBaseline(RecipeName::TYPED_PROPERTIES(), 'a')
                ],
                '[{"recipe_name":"typed-properties","baseline_hash":"a"}]',
            ];

        yield [
            [
                new RecipeBaseline(RecipeName::TYPED_PROPERTIES(), 'a'),
                new RecipeBaseline(RecipeName::UNUSED_PRIVATE_METHODS(), 'b'),
            ],
            '[{"recipe_name":"typed-properties","baseline_hash":"a"},{"recipe_name":"unused-private-methods","baseline_hash":"b"}]',
        ];
    }

    /**
     * @param array<RecipeBaseline> $expected
     * @dataProvider provideConvertToPHPValueData
     */
    public function testConvertToPHPValue(?string $value, ?array $expected): void
    {
        $platform = new PostgreSQL100Platform();
        $type = new DoctrineRecipeBaselinesArrayType();

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

        yield ['[]', []];

        yield [
            '[{"recipe_name":"typed-properties","baseline_hash":"a"}]',
            [
                new RecipeBaseline(RecipeName::TYPED_PROPERTIES(), 'a'),
            ],
        ];

        yield [
            '[{"recipe_name":"typed-properties","baseline_hash":"a"},{"recipe_name":"unused-private-methods","baseline_hash":"b"}]',
            [
                new RecipeBaseline(RecipeName::TYPED_PROPERTIES(), 'a'),
                new RecipeBaseline(RecipeName::UNUSED_PRIVATE_METHODS(), 'b'),
            ],
        ];
    }
}
