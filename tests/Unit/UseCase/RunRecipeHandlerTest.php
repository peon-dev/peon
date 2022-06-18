<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Lcobucci\Clock\FrozenClock;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\Event\JobScheduled;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\Cookbook\StaticRecipesCollection;
use Peon\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\UseCase\ExecuteJob;
use Peon\UseCase\RunRecipe;
use Peon\UseCase\RunRecipeHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class RunRecipeHandlerTest extends TestCase
{
    public function testRunningRecipeWillAddPendingJob(): void
    {
        $recipesCollection = new StaticRecipesCollection();
        $jobsCollection = new InMemoryJobsCollection();
        $projectsCollection = new InMemoryProjectsCollection();
        $commandBusSpy = $this->createMock(CommandBus::class);
        $commandBusSpy->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(ExecuteJob::class));
        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(JobScheduled::class));

        $projectId = new ProjectId('0');
        $ownerUserId = new UserId('0');
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();

        $project = new Project($projectId, $remoteGitRepository);
        $project->enableRecipe(RecipeName::TYPED_PROPERTIES);
        $projectsCollection->save(
            $project
        );

        $handler = new RunRecipeHandler(
            $projectsCollection,
            $recipesCollection,
            $jobsCollection,
            FrozenClock::fromUTC(),
            $commandBusSpy,
            $eventBusSpy,
        );

        $handler->__invoke(
            new RunRecipe(
                $projectId,
                RecipeName::TYPED_PROPERTIES
            )
        );

        $jobs = $jobsCollection->all();

        // Make sure there are no jobs in collection
        self::assertCount(1, $jobs);

        $job = $jobs[array_key_first($jobs)];

        self::assertNotNull($job->scheduledAt);
        self::assertNull($job->startedAt);
    }
}
