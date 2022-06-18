<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine\Type;

use Cron\CronExpression;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

// TODO: maybe we can use the package type (needs to check out!!)
final class DoctrineCronExpressionType extends Type
{
    public const NAME = 'cron_expression';


    public function getName(): string
    {
        return self::NAME;
    }


    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($column);
    }


    /**
     * @param CronExpression|null $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value->getExpression();
    }


    /**
     * @param string|null $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?CronExpression
    {
        if ($value === null) {
            return null;
        }

        return new CronExpression($value);
    }


    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
