<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineEnabledRecipesArrayType;
use PHPUnit\Framework\TestCase;

final class DoctrineEnabledRecipesArrayTypeTest extends TestCase
{
    /**
     * @param array<EnabledRecipe> $baselines
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideConvertToDatabaseValueData')]
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
    public static function provideConvertToDatabaseValueData(): \Generator
    {
        yield [null, null];

        yield [[], '[]'];

        yield [
                [
                    EnabledRecipe::withoutConfiguration(RecipeName::TYPED_PROPERTIES, 'a')
                ],
                '[{"recipe_name":"typed-properties","baseline_hash":"a","configuration":{"merge_automatically":false,"after_script":""}}]',
            ];

        yield [
            [
                EnabledRecipe::withoutConfiguration(RecipeName::TYPED_PROPERTIES, 'a'),
                new EnabledRecipe(RecipeName::UNUSED_PRIVATE_METHODS, 'b', new RecipeJobConfiguration(true, 'ls -la')),
            ],
            '[{"recipe_name":"typed-properties","baseline_hash":"a","configuration":{"merge_automatically":false,"after_script":""}},{"recipe_name":"unused-private-methods","baseline_hash":"b","configuration":{"merge_automatically":true,"after_script":"ls -la"}}]',
        ];
    }

    /**
     * @param array<EnabledRecipe> $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideConvertToPHPValueData')]
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
    public static function provideConvertToPHPValueData(): \Generator
    {
        yield [null, null];

        yield ['[]', []];

        yield [
            '[{"recipe_name":"typed-properties","baseline_hash":"a"}]',
            [
                EnabledRecipe::withoutConfiguration(RecipeName::TYPED_PROPERTIES, 'a'),
            ],
        ];

        yield [
            '[{"recipe_name":"typed-properties","baseline_hash":"a","configuration":{"merge_automatically":true,"after_script":"ls -la"}}]',
            [
                new EnabledRecipe(
                    RecipeName::TYPED_PROPERTIES,
                    'a',
                    new RecipeJobConfiguration(true, 'ls -la')
                ),
            ],
        ];

        yield [
            '[{"recipe_name":"typed-properties","baseline_hash":null},{"recipe_name":"unused-private-methods","baseline_hash":"a"}]',
            [
                EnabledRecipe::withoutConfiguration(RecipeName::TYPED_PROPERTIES, null),
                EnabledRecipe::withoutConfiguration(RecipeName::UNUSED_PRIVATE_METHODS, 'a'),
            ],
        ];
    }
}
