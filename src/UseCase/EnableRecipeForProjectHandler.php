<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EnableRecipeForProjectHandler  implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function __invoke(EnableRecipeForProject $command): void
    {
        $project = $this->projectsCollection->get($command->projectId);

        $project->enableRecipe($command->recipeName);

        $this->projectsCollection->save($project);
    }
}
