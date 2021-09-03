<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Project\ProjectId;

#[Immutable]
final class DeleteProjectCommand
{
    public function __construct(
        public ProjectId $projectId
    ) {}
}
