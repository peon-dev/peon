<?php

declare(strict_types=1);

namespace PHPMate\Packages\Enum;

use InvalidArgumentException;
use ReflectionClass;

abstract class Enum
{
    /**
     * @throws InvalidEnumValue
     */
    final protected function __construct(
        private string $value
    ) {
        if (!$this->hasOption($value)) {
            throw new InvalidEnumValue();
        }
    }


    /**
     * @return static
     * @throws InvalidEnumValue if the given option is out of range.
     */
    final public static function fromString(string $value): self
    {
        return new static($value);
    }


    final public function equals(?Enum $other): bool
    {
        return $other !== null && get_class($this) === get_class($other) && $this->toString() === $other->toString();
    }


    final public function toString(): string
    {
        return $this->value;
    }


    final protected function hasOption(string $value): bool
    {
        return in_array($value, static::getOptions(), true);
    }


    /**
     * @return array<string>
     */
    final protected static function getOptions(): array
    {
        $reflectionClass = new ReflectionClass(static::class);

        return array_values($reflectionClass->getConstants() ?? []);
    }
}

