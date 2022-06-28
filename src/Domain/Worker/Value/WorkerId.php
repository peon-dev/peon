<?php

declare(strict_types=1);

namespace Peon\Domain\Worker\Value;

final class WorkerId implements \Stringable
{
    public function __construct(public readonly string $id)
    {}


    public function __toString(): string
    {
        return $this->id;
    }
}
