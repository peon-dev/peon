<?php

declare(strict_types=1);

namespace PHPMate\Domain\Task\Value;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class TaskId implements \Stringable
{
    public function __construct(public string $id)
    {}


    public function __toString(): string
    {
        return $this->id;
    }
}
