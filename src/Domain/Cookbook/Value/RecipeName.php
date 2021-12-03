<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook\Value;

use PHPMate\Packages\Enum\Enum;

final class RecipeName extends Enum
{
    public const UNUSED_PRIVATE_METHODS = 'unused-private-methods';
    public const TYPED_PROPERTIES = 'typed-properties';


    public static function UNUSED_PRIVATE_METHODS(): self
    {
        return new self(self::UNUSED_PRIVATE_METHODS);
    }


    public static function TYPED_PROPERTIES(): self
    {
        return new self(self::TYPED_PROPERTIES);
    }
}
