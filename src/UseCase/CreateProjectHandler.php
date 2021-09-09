<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectsCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CreateProjectHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection
    ) {}

    // @TODO: throws when could not clone
    public function __invoke(CreateProject $createProject): void
    {
        $project = new Project(
            $this->projectsCollection->nextIdentity(),
            $createProject->remoteGitRepository,
        );

        $this->projectsCollection->save($project);
    }
}
