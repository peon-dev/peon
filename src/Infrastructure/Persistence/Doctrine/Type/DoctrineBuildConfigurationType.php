<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Peon\Domain\PhpApplication\Value\PhpApplicationBuildConfiguration;

final class DoctrineBuildConfigurationType extends JsonType
{
    public const NAME = 'build_configuration';

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
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): PhpApplicationBuildConfiguration
    {
        if ($value === null || $value === '[]' || $value === '{}}') {
            return PhpApplicationBuildConfiguration::createDefault();
        }

        $jsonData = parent::convertToPHPValue($value, $platform);
        assert(is_array($jsonData));

        if (empty($jsonData)) {
            return PhpApplicationBuildConfiguration::createDefault();
        }

        // TODO: what about some hydrator instead of doing it manually?
        return new PhpApplicationBuildConfiguration(
            $jsonData['skip_composer_install'] ?? PhpApplicationBuildConfiguration::DEFAULT_SKIP_COMPOSER_INSTALL_VALUE,
        );
    }

    /**
     * @param PhpApplicationBuildConfiguration $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if (!is_a($value, PhpApplicationBuildConfiguration::class)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), [PhpApplicationBuildConfiguration::class]);
        }

        // TODO: what about some hydrator instead of doing it manually?
        $data = [
            'skip_composer_install' => $value->skipComposerInstall,
        ];

        $converted = parent::convertToDatabaseValue($data, $platform);

        if (is_string($converted) === false) {
            throw ConversionException::conversionFailedSerialization($value, 'json', 'Invalid json format');
        }

        return $converted;
    }
}
