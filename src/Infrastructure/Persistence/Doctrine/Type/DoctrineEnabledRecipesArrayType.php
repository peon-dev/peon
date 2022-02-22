<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\EnabledRecipe;

final class DoctrineEnabledRecipesArrayType extends JsonType
{
    public const NAME = 'enabled_recipes_array';

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    /**
     * @return null|array<EnabledRecipe>
     *
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        if ($value === null) {
            return null;
        }

        $jsonData = parent::convertToPHPValue($value, $platform);
        assert(is_array($jsonData));

        $enabledRecipes = [];

        foreach ($jsonData as $baselineData) {
            // TODO: what about some hydrator instead of doing it manually?
            $enabledRecipes[] = EnabledRecipe::withoutConfiguration(
                RecipeName::from($baselineData['recipe_name']),
                $baselineData['baseline_hash'],
            );
        }

        return $enabledRecipes;
    }

    /**
     * @param null|array<EnabledRecipe> $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        $data = [];

        foreach ($value as $baseline) {
            if (!is_a($baseline, EnabledRecipe::class)) {
                throw ConversionException::conversionFailedInvalidType($value, $this->getName(), [EnabledRecipe::class]);
            }

            $data[] = [
                'recipe_name' => $baseline->recipeName->value,
                'baseline_hash' => $baseline->baselineHash,
            ];
        }

        $converted = parent::convertToDatabaseValue($data, $platform);

        if (is_string($converted) === false && $converted !== null) {
            throw ConversionException::conversionFailedSerialization($value, 'json', 'Invalid json format');
        }

        return $converted;
    }
}
