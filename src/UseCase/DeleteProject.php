<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;

final class DeleteProject
{
    public function __construct(
        private ProjectsCollection $projectsCollection
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function handle(DeleteProjectCommand $command): void
    {
        $this->projectsCollection->remove($command->projectId);
    }
}
