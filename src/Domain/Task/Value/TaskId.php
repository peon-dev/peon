<?php

declare(strict_types=1);

namespace Peon\Domain\Task\Value;

final class TaskId implements \Stringable
{
    public function __construct(public readonly string $id)
    {}


    public function __toString(): string
    {
        return $this->id;
    }
}
