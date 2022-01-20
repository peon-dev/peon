<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Peon\Domain\PhpApplication\Value\BuildConfiguration;

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
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): BuildConfiguration
    {
        if ($value === null || $value === '[]' || $value === '{}}') {
            return BuildConfiguration::createDefault();
        }

        $jsonData = parent::convertToPHPValue($value, $platform);
        assert(is_array($jsonData));

        if (empty($jsonData)) {
            return BuildConfiguration::createDefault();
        }

        // TODO: what about some hydrator instead of doing it manually?
        return new BuildConfiguration(
            $jsonData['skip_composer_install'] ?? false,
        );
    }

    /**
     * @param BuildConfiguration $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if (!is_a($value, BuildConfiguration::class)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), [BuildConfiguration::class]);
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
