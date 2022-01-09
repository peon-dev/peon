<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Cookbook\Event\RecipeEnabled;
use PHPMate\Domain\Cookbook\Exception\RecipeNotFound;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use PHPMate\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EnableRecipeWithBaselineForProjectHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private RecipesCollection $recipesCollection,
        private GetLastCommitOfDefaultBranch $getLastCommitOfDefaultBranch,
        private EventBus $eventBus,
    )
    {
    }


    /**
     * @throws RecipeNotFound
     * @throws ProjectNotFound
     * @throws GitProviderCommunicationFailed
     */
    public function __invoke(EnableRecipeWithBaselineForProject $command): void
    {
        if ($this->recipesCollection->hasRecipeWithName($command->recipeName) === false) {
            throw new RecipeNotFound();
        }

        $project = $this->projectsCollection->get($command->projectId);
        $lastCommit = $this->getLastCommitOfDefaultBranch->getLastCommitOfDefaultBranch($project->remoteGitRepository);

        $project->enableRecipe($command->recipeName, $lastCommit->hash);

        $this->projectsCollection->save($project);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new RecipeEnabled(
                $project->projectId,
                $command->recipeName,
            )
        );
    }
}
