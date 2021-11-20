<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job\Value;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class JobId implements \Stringable
{
    public function __construct(public string $id)
    {}


    public function __toString(): string
    {
        return $this->id;
    }
}
