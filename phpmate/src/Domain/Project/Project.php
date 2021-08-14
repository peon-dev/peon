<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

#[Immutable]
class Project
{
    public function __construct(
        public ProjectId $projectId,
        public string $name,
        public RemoteGitRepository $remoteGitRepository
    ) {}
}
