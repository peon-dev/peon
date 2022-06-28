<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\Clock\SystemClock;
use Peon\Domain\Job\Event\JobScheduled;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\UseCase\ExecuteJob;
use Peon\UseCase\RunTaskHandler;
use Peon\UseCase\RunTask;
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
            ->with($this->isInstanceOf(ExecuteJob::class));
        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(JobScheduled::class));

        $projectId = new ProjectId('0');
        $ownerUserId = new UserId('0');
        $taskId = new TaskId('0');
        $jobId = new JobId('0');
        $remoteGitRepository = DataFixtures::createRemoteGitRepository();

        $projectsCollection->save(
            new Project($projectId, $remoteGitRepository, $ownerUserId)
        );

        $tasksCollection->save(
            new Task($taskId, $projectId, 'Task', ['command'], false)
        );

        self::assertCount(0, $jobsCollection->all());

        $useCase = new RunTaskHandler(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC(),
            $commandBusSpy,
            $eventBusSpy,
        );

        $useCase->__invoke(
            new RunTask($taskId, $jobId)
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
        $dummyEventBus = $this->createMock(EventBus::class);

        $useCase = new RunTaskHandler(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC(),
            $dummyCommandBus,
            $dummyEventBus,
        );

        $useCase->__invoke(
            new RunTask(new TaskId('0'), new JobId('0'))
        );
    }


    public function testCanNotRunTaskWithNonExistingProject(): void
    {
        $this->expectException(ProjectNotFound::class);

        $jobsCollection = new InMemoryJobsCollection();
        $projectsCollection = new InMemoryProjectsCollection();
        $tasksCollection = new InMemoryTasksCollection();
        $dummyCommandBus = $this->createMock(CommandBus::class);
        $dummyEventBus = $this->createMock(EventBus::class);

        $taskId = new TaskId('0');
        $projectId = new ProjectId('0');
        $jobId = new JobId('0');

        $tasksCollection->save(
            new Task($taskId, $projectId, 'Task', ['command'], false)
        );

        $useCase = new RunTaskHandler(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC(),
            $dummyCommandBus,
            $dummyEventBus,
        );

        $useCase->__invoke(
            new RunTask($taskId, $jobId)
        );
    }
}
