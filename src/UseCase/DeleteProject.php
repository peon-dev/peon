<?php

declare(strict_types=1);

namespace Peon\UseCase;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Project\Value\ProjectId;

final class DeleteProject
{
    public function __construct(
        public readonly ProjectId $projectId,
    ) {}
}
