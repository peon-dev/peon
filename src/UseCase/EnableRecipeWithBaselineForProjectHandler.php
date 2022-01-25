<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Cookbook\Event\RecipeEnabled;
use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class EnableRecipeWithBaselineForProjectHandler implements CommandHandlerInterface
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
        $lastCommit = $this->getLastCommitOfDefaultBranch->forRemoteGitRepository($project->remoteGitRepository);

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
