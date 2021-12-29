<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use Lcobucci\Clock\FrozenClock;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Infrastructure\Cookbook\StaticRecipesCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\UseCase\ExecuteJob;
use PHPMate\UseCase\RunRecipe;
use PHPMate\UseCase\RunRecipeHandler;
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

        $projectId = new ProjectId('0');
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();

        $projectsCollection->save(
            new Project($projectId, $remoteGitRepository)
        );

        $handler = new RunRecipeHandler(
            $projectsCollection,
            $recipesCollection,
            $jobsCollection,
            FrozenClock::fromUTC(),
            $commandBusSpy,
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
