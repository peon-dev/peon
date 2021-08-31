<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

#[Immutable]
class Project
{
    // @TODO: assert can connect to repository
    public function __construct(
        public ProjectId $projectId,
        public RemoteGitRepository $remoteGitRepository
    ) {}
}
