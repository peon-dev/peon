<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Cookbook\Exception\RecipeNotEnabled;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\Job\Event\JobScheduled;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class RunRecipeHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ProjectsCollection $projectsCollection,
        private readonly RecipesCollection $recipesCollection,
        private readonly JobsCollection $jobsCollection,
        private readonly Clock $clock,
        private readonly CommandBus $commandBus,
        private readonly EventBus $eventBus,
    ) {
    }


    /**
     * @throws ProjectNotFound
     * @throws JobNotFound
     * @throws JobHasFinishedAlready
     * @throws JobHasStartedAlready
     * @throws JobHasNotStartedYet
     * @throws JobExecutionFailed
     * @throws RecipeNotEnabled
     */
    public function __invoke(RunRecipe $command): void
    {
        $project = $this->projectsCollection->get($command->projectId);
        $recipe = $this->recipesCollection->get($command->recipeName);

        $jobId = $this->jobsCollection->nextIdentity();
        $enabledRecipe = $project->getEnabledRecipe($command->recipeName);

        $job = Job::scheduleFromRecipe(
            $jobId,
            $project->projectId,
            $this->clock,
            $recipe->title,
            $enabledRecipe
        );

        $this->jobsCollection->save($job);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new JobScheduled($jobId, $project->projectId)
        );

        // TODO: should be event instead, because this is handled asynchronously
        $this->commandBus->dispatch(
            new ExecuteJob($jobId, $enabledRecipe->configuration->mergeAutomatically)
        );
    }
}
