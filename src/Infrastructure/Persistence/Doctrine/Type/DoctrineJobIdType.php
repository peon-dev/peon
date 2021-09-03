<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use PHPMate\Domain\Job\JobId;

final class DoctrineJobIdType extends Type
{
    public const NAME = 'job_id';


    public function getName(): string
    {
        return self::NAME;
    }


    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }


    /**
     * @param JobId $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return $value->id;
    }


    /**
     * @param string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): JobId
    {
        return new JobId($value);
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
