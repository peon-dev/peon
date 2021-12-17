<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\EnabledRecipe;
use PHPMate\Infrastructure\Persistence\Doctrine\Type\DoctrineEnabledRecipesArrayType;
use PHPUnit\Framework\TestCase;

final class DoctrineEnabledRecipesArrayTypeTest extends TestCase
{
    /**
     * @param array<EnabledRecipe> $baselines
     * @dataProvider provideConvertToDatabaseValueData
     */
    public function testConvertToDatabaseValue(?array $baselines, ?string $expected): void
    {
        $platform = new PostgreSQL100Platform();
        $type = new DoctrineEnabledRecipesArrayType();

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
                    new EnabledRecipe(RecipeName::TYPED_PROPERTIES, 'a')
                ],
                '[{"recipe_name":"typed-properties","baseline_hash":"a"}]',
            ];

        yield [
            [
                new EnabledRecipe(RecipeName::TYPED_PROPERTIES, 'a'),
                new EnabledRecipe(RecipeName::UNUSED_PRIVATE_METHODS, 'b'),
            ],
            '[{"recipe_name":"typed-properties","baseline_hash":"a"},{"recipe_name":"unused-private-methods","baseline_hash":"b"}]',
        ];
    }

    /**
     * @param array<EnabledRecipe> $expected
     * @dataProvider provideConvertToPHPValueData
     */
    public function testConvertToPHPValue(?string $value, ?array $expected): void
    {
        $platform = new PostgreSQL100Platform();
        $type = new DoctrineEnabledRecipesArrayType();

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
                new EnabledRecipe(RecipeName::TYPED_PROPERTIES, 'a'),
            ],
        ];

        yield [
            '[{"recipe_name":"typed-properties","baseline_hash":null},{"recipe_name":"unused-private-methods","baseline_hash":"a"}]',
            [
                new EnabledRecipe(RecipeName::TYPED_PROPERTIES, null),
                new EnabledRecipe(RecipeName::UNUSED_PRIVATE_METHODS, 'a'),
            ],
        ];
    }
}
