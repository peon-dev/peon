<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\EnabledRecipe;

final class DoctrineEnabledRecipeType extends JsonType
{
    public const NAME = 'enabled_recipe';

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
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): EnabledRecipe|null
    {
        if ($value === null || $value === '[]' || $value === '{}}') {
            return null;
        }

        $jsonData = parent::convertToPHPValue($value, $platform);
        assert(is_array($jsonData));

        if (empty($jsonData)) {
            return null;
        }

        // TODO: what about some hydrator instead of doing it manually?
        return new EnabledRecipe(
            RecipeName::from($jsonData['recipe_name']),
            $jsonData['baseline_hash'],
        );
    }

    /**
     * @param null|EnabledRecipe $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_a($value, EnabledRecipe::class)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), [EnabledRecipe::class]);
        }

        // TODO: what about some hydrator instead of doing it manually?
        $data = [
            'recipe_name' => $value->recipeName->value,
            'baseline_hash' => $value->baselineHash,
        ];

        $converted = parent::convertToDatabaseValue($data, $platform);

        if (is_string($converted) === false && $converted !== null) {
            throw ConversionException::conversionFailedSerialization($value, 'json', 'Invalid json format');
        }

        return $converted;
    }
}
