<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\Project\Value\EnabledRecipe;

final class DoctrineMergeRequestType extends JsonType
{
    public const NAME = 'merge_request';

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
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): MergeRequest|null
    {
        if ($value === null || $value === '{}') {
            return null;
        }

        $jsonData = parent::convertToPHPValue($value, $platform);
        assert(is_array($jsonData));

        if (empty($jsonData)) {
            return null;
        }

        // TODO: what about some hydrator instead of doing it manually?
        return new MergeRequest(
            $jsonData['url'],
        );
    }

    /**
     * @param null|MergeRequest $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_a($value, MergeRequest::class)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), [MergeRequest::class]);
        }

        // TODO: what about some hydrator instead of doing it manually?
        $data = [
            'url' => $value->url,
        ];

        $converted = parent::convertToDatabaseValue($data, $platform);

        if (is_string($converted) === false && $converted !== null) {
            throw ConversionException::conversionFailedSerialization($value, 'json', 'Invalid json format');
        }

        return $converted;
    }
}
