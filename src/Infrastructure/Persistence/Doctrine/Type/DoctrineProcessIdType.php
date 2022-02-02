<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Peon\Domain\Process\Value\ProcessId;

final class DoctrineProcessIdType extends Type
{
    public const NAME = 'process_id';


    public function getName(): string
    {
        return self::NAME;
    }


    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }


    /**
     * @param ProcessId $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return $value->id;
    }


    /**
     * @param string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ProcessId
    {
        return new ProcessId($value);
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
