<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class ProjectId implements \Stringable
{
    public function __construct(
        public string $id
    ) {}


    public function __toString()
    {
        return $this->id;
    }
}
