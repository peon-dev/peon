<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

#[Immutable]
class Project
{
    public string $name;

    // @TODO: assert can connect to repository
    public function __construct(
        public ProjectId $projectId,
        public RemoteGitRepository $remoteGitRepository
    ) {
        $this->name = $this->remoteGitRepository->getProject();
    }


    public function enableRecipe(): void
    {
    }
}
