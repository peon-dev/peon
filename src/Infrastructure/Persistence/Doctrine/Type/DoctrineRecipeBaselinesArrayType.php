<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\RecipeBaseline;

final class DoctrineRecipeBaselinesArrayType extends JsonType
{
    public const NAME = 'recipe_baselines_array';

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
     * @param null|string $value
     * @throws ConversionException
     * @return null|array<RecipeBaseline>
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        if ($value === null) {
            return null;
        }

        $jsonData = parent::convertToPHPValue($value, $platform);

        if (!is_array($jsonData)) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        $baselines = [];

        foreach ($jsonData as $baselineData) {
            $baselines[] = new RecipeBaseline(
                RecipeName::fromString($baselineData['recipe_name']),
                $baselineData['baseline_hash'],
            );
        }

        return $baselines;
    }

    /**
     * @param null|array<RecipeBaseline> $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        $data = [];

        foreach ($value as $baseline) {
            if (!is_a($baseline, RecipeBaseline::class)) {
                throw ConversionException::conversionFailedInvalidType($value, $this->getName(), [RecipeBaseline::class]);
            }

            $data[] = [
                'recipe_name' => $baseline->recipeName->toString(),
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
