<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook\Value;

use PHPMate\Packages\Enum\Enum;

final class RecipeName extends Enum implements \Stringable
{
    public const UNUSED_PRIVATE_METHODS = 'unused-private-methods';
    public const TYPED_PROPERTIES = 'typed-properties';
    public const SWITCH_TO_MATCH = 'switch-to-match';


    public static function UNUSED_PRIVATE_METHODS(): self
    {
        return new self(self::UNUSED_PRIVATE_METHODS);
    }


    public static function TYPED_PROPERTIES(): self
    {
        return new self(self::TYPED_PROPERTIES);
    }


    public static function SWITCH_TO_MATCH(): self
    {
        return new self(self::SWITCH_TO_MATCH);
    }


    public function __toString(): string
    {
        return $this->toString();
    }
}
