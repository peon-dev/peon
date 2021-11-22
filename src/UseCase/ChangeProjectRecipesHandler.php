<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ChangeProjectRecipesHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function __invoke(ChangeProjectRecipes $command): void
    {
        $project = $this->projectsCollection->get($command->projectId);

        $project->changeRecipes($command->recipes);

        $this->projectsCollection->save($project);
    }
}
