<?php

declare(strict_types=1);

namespace Peon\Domain\Project\Value;

final class ProjectId implements \Stringable
{
    public function __construct(
        public readonly string $id
    ) {}


    public function __toString()
    {
        return $this->id;
    }


    public function isSameAs(ProjectId $other): bool
    {
        return $other->id === $this->id;
    }
}
