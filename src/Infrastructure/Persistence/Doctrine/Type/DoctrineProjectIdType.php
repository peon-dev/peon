<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Peon\Domain\Project\Value\ProjectId;

final class DoctrineProjectIdType extends Type
{
    public const NAME = 'project_id';


    public function getName(): string
    {
        return self::NAME;
    }


    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }


    /**
     * @param ProjectId $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return $value->id;
    }


    /**
     * @param string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ProjectId
    {
        return new ProjectId($value);
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
