<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use PHPMate\Domain\Task\Value\TaskId;

final class DoctrineTaskIdType extends Type
{
    public const NAME = 'task_id';


    public function getName(): string
    {
        return self::NAME;
    }


    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }


    /**
     * @param null|TaskId $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value->id;
    }


    /**
     * @param ?string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?TaskId
    {
        if ($value === null) {
            return null;
        }

        return new TaskId($value);
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
