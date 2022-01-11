<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Infrastructure\Persistence\Doctrine\Type\DoctrineEnabledRecipeType;
use PHPUnit\Framework\TestCase;

final class DoctrineEnabledRecipeTypeTest extends TestCase
{
    /**
     * @dataProvider provideConvertToDatabaseValueData
     */
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
    public function provideConvertToDatabaseValueData(): \Generator
    {
        yield [null, null];

        yield [
            new EnabledRecipe(RecipeName::TYPED_PROPERTIES, 'a'),
            '{"recipe_name":"typed-properties","baseline_hash":"a"}',
        ];
    }

    /**
     * @dataProvider provideConvertToPHPValueData
     */
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
    public function provideConvertToPHPValueData(): \Generator
    {
        yield [null, null];

        yield ['[]', null];

        yield [
            '{"recipe_name":"typed-properties","baseline_hash":"a"}',
            new EnabledRecipe(RecipeName::TYPED_PROPERTIES, 'a'),
        ];
    }
}
