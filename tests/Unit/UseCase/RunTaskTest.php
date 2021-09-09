<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\Clock\SystemClock;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\UseCase\RunTask;
use PHPMate\UseCase\RunTaskCommand;
use PHPUnit\Framework\TestCase;

final class RunTaskTest extends TestCase
{
    public function testTaskCanRunAndJobWillBeScheduled(): void
    {
        $jobsCollection = new InMemoryJobsCollection();
        $projectsCollection = new InMemoryProjectsCollection();
        $tasksCollection = new InMemoryTasksCollection();

        $projectId = new ProjectId('0');
        $taskId = new TaskId('0');
        $remoteGitRepository = new RemoteGitRepository(
            'https://gitlab.com/phpmate/phpmate.git',
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );

        $projectsCollection->save(
            new Project($projectId, $remoteGitRepository)
        );

        $tasksCollection->save(
            new Task($taskId, $projectId, 'Task', ['command'])
        );

        self::assertCount(0, $jobsCollection->all());

        $useCase = new RunTask(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC()
        );

        $useCase->__invoke(
            new RunTaskCommand($taskId)
        );

        self::assertCount(1, $jobsCollection->all());
    }


    public function testCanNotRunNonExistingTask(): void
    {
        $this->expectException(TaskNotFound::class);

        $jobsCollection = new InMemoryJobsCollection();
        $projectsCollection = new InMemoryProjectsCollection();
        $tasksCollection = new InMemoryTasksCollection();

        $useCase = new RunTask(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC()
        );

        $useCase->__invoke(
            new RunTaskCommand(new TaskId('0'))
        );
    }


    public function testCanNotRunTaskWithNonExistingProject(): void
    {
        $this->expectException(ProjectNotFound::class);

        $jobsCollection = new InMemoryJobsCollection();
        $projectsCollection = new InMemoryProjectsCollection();
        $tasksCollection = new InMemoryTasksCollection();

        $taskId = new TaskId('0');
        $projectId = new ProjectId('0');

        $tasksCollection->save(
            new Task($taskId, $projectId, 'Task', ['command'])
        );

        $useCase = new RunTask(
            $tasksCollection,
            $jobsCollection,
            $projectsCollection,
            FrozenClock::fromUTC()
        );

        $useCase->__invoke(
            new RunTaskCommand($taskId)
        );
    }
}
