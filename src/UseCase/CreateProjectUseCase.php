<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectsCollection;

final class CreateProjectUseCase
{
    public function __construct(
        private ProjectsCollection $projectsCollection
    ) {}

    // @TODO: throws
    public function __invoke(CreateProject $createProject): void
    {
        $project = new Project(
            $this->projectsCollection->provideNextIdentity(),
            $createProject->remoteGitRepository,
        );

        $this->projectsCollection->save($project);
    }
}
