<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Peon\Domain\Worker\Value\WorkerId;

final class DoctrineWorkerIdType extends Type
{
    public const NAME = 'worker_id';


    public function getName(): string
    {
        return self::NAME;
    }


    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }


    /**
     * @param null|WorkerId $value
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
    public function convertToPHPValue($value, AbstractPlatform $platform): ?WorkerId
    {
        if ($value === null) {
            return null;
        }

        return new WorkerId($value);
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
