<?php

declare(strict_types=1);

namespace Peon\UseCase;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Project\Value\ProjectId;

#[Immutable]
final class DeleteProject
{
    public function __construct(
        public ProjectId $projectId
    ) {}
}
