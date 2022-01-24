<?php

declare(strict_types=1);

namespace Peon\Domain\Process\Value;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class ProcessId implements \Stringable
{
    public function __construct(public string $id)
    {}


    public function __toString(): string
    {
        return $this->id;
    }
}
