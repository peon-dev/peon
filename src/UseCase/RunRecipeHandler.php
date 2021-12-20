<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Job\Exception\JobExecutionFailed;
use PHPMate\Domain\Job\Exception\JobHasFinishedAlready;
use PHPMate\Domain\Job\Exception\JobHasNoCommands;
use PHPMate\Domain\Job\Exception\JobHasNotStartedYet;
use PHPMate\Domain\Job\Exception\JobHasStartedAlready;
use PHPMate\Domain\Job\Exception\JobNotFound;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RunRecipeHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private RecipesCollection $recipesCollection,
        private JobsCollection $jobsCollection,
        private Clock $clock,
        private CommandBus $commandBus,
    ) {
    }


    /**
     * @throws ProjectNotFound
     * @throws JobHasNoCommands
     * @throws JobNotFound
     * @throws JobHasFinishedAlready
     * @throws JobHasStartedAlready
     * @throws JobHasNotStartedYet
     * @throws JobExecutionFailed
     */
    public function __invoke(RunRecipe $command): void
    {
        $project = $this->projectsCollection->get($command->projectId);
        $recipe = $this->recipesCollection->get($command->recipeName);

        $jobId = $this->jobsCollection->nextIdentity();

        $job = Job::scheduleFromRecipe(
            $jobId,
            $project->projectId,
            $recipe,
            $this->clock
        );

        $this->jobsCollection->save($job);

        // TODO: should be event instead, because this is handled asynchronously
        $this->commandBus->dispatch(
            new ExecuteTaskJob($jobId)
        );
    }
}
