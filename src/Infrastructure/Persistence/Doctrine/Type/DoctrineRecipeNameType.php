<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use PHPMate\Domain\Cookbook\Value\RecipeName;

final class DoctrineRecipeNameType extends StringType
{
    public const NAME = 'recipe_name';


    public function getName(): string
    {
        return self::NAME;
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }


    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): RecipeName|null
    {
        if ($value === null) {
            return null;
        }

        assert(is_string($value));

        return RecipeName::from($value);
    }

    /**
     * @param null|RecipeName $value
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value->value;
    }
}
