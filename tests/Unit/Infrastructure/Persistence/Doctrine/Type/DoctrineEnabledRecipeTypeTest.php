<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineEnabledRecipeType;
use PHPUnit\Framework\TestCase;

final class DoctrineEnabledRecipeTypeTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideConvertToDatabaseValueData')]
    public function testConvertToDatabaseValue(?EnabledRecipe $enabledRecipe, ?string $expected): void
    {
        $platform = new PostgreSQL100Platform();
        $type = new DoctrineEnabledRecipeType();

        $actual = $type->convertToDatabaseValue($enabledRecipe, $platform);

        self::assertEquals($expected, $actual);
    }

    /**
     * @return \Generator<array<mixed>>
     */
    public static function provideConvertToDatabaseValueData(): \Generator
    {
        yield [null, null];

        yield [
            EnabledRecipe::withoutConfiguration(RecipeName::TYPED_PROPERTIES, 'a'),
            '{"recipe_name":"typed-properties","baseline_hash":"a","configuration":{"merge_automatically":false,"after_script":""}}',
        ];
        yield [
            new EnabledRecipe(RecipeName::TYPED_PROPERTIES, 'a', new RecipeJobConfiguration(true, 'ls -la')),
            '{"recipe_name":"typed-properties","baseline_hash":"a","configuration":{"merge_automatically":true,"after_script":"ls -la"}}',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideConvertToPHPValueData')]
    public function testConvertToPHPValue(?string $value, ?EnabledRecipe $expected): void
    {
        $platform = new PostgreSQL100Platform();
        $type = new DoctrineEnabledRecipeType();

        $actual = $type->convertToPHPValue($value, $platform);

        // Non-strict compare
        self::assertEquals($expected, $actual);
    }


    /**
     * @return \Generator<array{string|null, EnabledRecipe|null}>
     */
    public static function provideConvertToPHPValueData(): \Generator
    {
        yield [null, null];

        yield ['[]', null];

        yield [
            '{"recipe_name":"typed-properties","baseline_hash":"a"}',
            EnabledRecipe::withoutConfiguration(RecipeName::TYPED_PROPERTIES, 'a'),
        ];

        yield [
            '{"recipe_name":"typed-properties","baseline_hash":"a","configuration":{"merge_automatically":true,"after_script":"ls -la"}}',
            new EnabledRecipe(RecipeName::TYPED_PROPERTIES, 'a', new RecipeJobConfiguration(true, 'ls -la')),
        ];
    }
}
