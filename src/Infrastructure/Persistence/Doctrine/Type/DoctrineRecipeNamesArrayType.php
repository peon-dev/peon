<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPMate\Domain\Cookbook\Value\RecipeName;

final class DoctrineRecipeNamesArrayType extends Type
{
    /**
     * @see http://stackoverflow.com/a/19082849/1160901
     */
    private const ARRAY_PATTERN = '/(?<=^\{|,)(([^,"{]*)|\s*"((?:[^"\\\\]|\\\\(?:.|[0-9]+|x[0-9a-f]+))*)"\s*)(,|(?<!^\{)(?=\}$))/i';

    public const NAME = 'recipe_names_array';


    public function getName(): string
    {
        return self::NAME;
    }


    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($column) . ' ARRAY';
    }


    /**
     * @param null|array<RecipeName> $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'array']);
        }

        if ($value === []) {
            return '{}';
        }

        $recipeNamesArray = array_map(static function (RecipeName $recipeName) {
            return addcslashes($recipeName->toString(), '"');
        }, $value);

        return '{"' . implode('","', $recipeNamesArray) . '"}';
    }


    /**
     * @param null|string $value
     * @return null|array<RecipeName>
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'string']);
        }

        if ($value === '' || '{}' === $value) {
            return [];
        }

        preg_match_all(self::ARRAY_PATTERN, $value, $matches, PREG_SET_ORDER);

        $array = [];
        foreach ($matches as $match) {
            if ('' !== $match[3]) {
                $array[] = RecipeName::fromString(stripcslashes($match[3]));
                continue;
            }

            $array[] = RecipeName::fromString($match[2]);
        }

        return $array;
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
