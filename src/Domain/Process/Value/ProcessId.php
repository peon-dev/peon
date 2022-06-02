<?php

declare(strict_types=1);

namespace Peon\Domain\Process\Value;

final class ProcessId implements \Stringable
{
    public function __construct(public readonly string $id)
    {}


    public function __toString(): string
    {
        return $this->id;
    }
}
