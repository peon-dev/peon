<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\Clock\SystemClock;
use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\UseCase\ExecuteJob;
use PHPMate\UseCase\RunTaskHandler;
use PHPMate\UseCase\RunTask;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class RunTaskHandlerTest extends TestCase
{
    public function testTaskCanRunAndJobWillBeScheduled(): void
    {
        $jobsCollection = new InMemoryJobsCollection();
        $projectsCollection = new InMemoryProjectsCollection();
        $tasksCollection = new InMemoryTasksCollection();
        $commandBusSpy = $this->createMock(CommandBus::class);
        $commandBusSpy->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(ExecuteJob::class));

        $projectId = new ProjectId('0');
        $taskId = new TaskId('0');
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();

        $projectsCollection->save(
            new Project($projectId, $remoteGitRepository)
        );

        $tasksCollection->save(
            new Task($taskId, $projectId, 'Task', ['command'])
        );

        self::assertCount(0, $jobsCollection->all());

        $useCase = new RunTaskHandler(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC(),
            $commandBusSpy
        );

        $useCase->__invoke(
            new RunTask($taskId)
        );

        self::assertCount(1, $jobsCollection->all());
    }


    public function testCanNotRunNonExistingTask(): void
    {
        $this->expectException(TaskNotFound::class);

        $jobsCollection = new InMemoryJobsCollection();
        $projectsCollection = new InMemoryProjectsCollection();
        $tasksCollection = new InMemoryTasksCollection();
        $dummyCommandBus = $this->createMock(CommandBus::class);

        $useCase = new RunTaskHandler(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC(),
            $dummyCommandBus
        );

        $useCase->__invoke(
            new RunTask(new TaskId('0'))
        );
    }


    public function testCanNotRunTaskWithNonExistingProject(): void
    {
        $this->expectException(ProjectNotFound::class);

        $jobsCollection = new InMemoryJobsCollection();
        $projectsCollection = new InMemoryProjectsCollection();
        $tasksCollection = new InMemoryTasksCollection();
        $dummyCommandBus = $this->createMock(CommandBus::class);

        $taskId = new TaskId('0');
        $projectId = new ProjectId('0');

        $tasksCollection->save(
            new Task($taskId, $projectId, 'Task', ['command'])
        );

        $useCase = new RunTaskHandler(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC(),
            $dummyCommandBus
        );

        $useCase->__invoke(
            new RunTask($taskId)
        );
    }
}
