<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Lcobucci\Clock\FrozenClock;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\Event\JobScheduled;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\Task\Value\TaskId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\ExecuteJob;
use Peon\UseCase\RerunJob;
use Peon\UseCase\RerunJobHandler;
use PHPUnit\Framework\TestCase;

final class RerunJobHandlerTest extends TestCase
{
    public function testJobWithRecipeWillBeScheduled(): void
    {
        $clock = FrozenClock::fromUTC();
        $originalJobId = new JobId('1');
        $newJobId = new JobId('2');
        $configuration = RecipeJobConfiguration::createDefault();
        $jobsCollection = $this->createMock(JobsCollection::class);
        $jobWithRecipe = new Job(
            $originalJobId,
            new ProjectId(''),
            'title',
            ['command'],
            $clock,
            new EnabledRecipe(RecipeName::CONSTRUCTOR_PROPERTY_PROMOTION, null, $configuration),
            null,
        );
        $jobsCollection->expects(self::once())
            ->method('get')
            ->willReturn($jobWithRecipe);

        $commandBusSpy = $this->createMock(CommandBus::class);
        $commandBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->callback(static function(ExecuteJob $command) use ($newJobId, $configuration): bool {
                self::assertSame($configuration->mergeAutomatically, $command->mergeAutomatically);
                self::assertSame($command->jobId, $newJobId);

                return true;
            }));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->callback(static function(JobScheduled $event) use ($newJobId): bool {
                return $event->jobId === $newJobId;
            }));

        $tasksCollection = $this->createMock(TasksCollection::class);
        $tasksCollection->expects(self::never())->method('get');

        $handler = new RerunJobHandler(
            $jobsCollection,
            $clock,
            $commandBusSpy,
            $eventBusSpy,
            $tasksCollection,
        );

        $handler->__invoke(
            new RerunJob($originalJobId, $newJobId)
        );
    }


    public function testNonExistingTaskWillThrowException(): void
    {
        $clock = FrozenClock::fromUTC();
        $originalJobId = new JobId('1');
        $newJobId = new JobId('2');
        $jobsCollection = $this->createMock(JobsCollection::class);
        $taskId = new TaskId('1');
        $jobWithTask = new Job(
            $originalJobId,
            new ProjectId(''),
            'title',
            ['command'],
            $clock,
            null,
            $taskId,
        );
        $jobsCollection->expects(self::once())
            ->method('get')
            ->willReturn($jobWithTask);

        $commandBusSpy = $this->createMock(CommandBus::class);
        $commandBusSpy->expects(self::never())->method('dispatch');

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::never())->method('dispatch');

        $tasksCollection = $this->createMock(TasksCollection::class);
        $tasksCollection->expects(self::once())
            ->method('get')
            ->with($taskId)
            ->willThrowException(
                new TaskNotFound(),
            );

        $handler = new RerunJobHandler(
            $jobsCollection,
            $clock,
            $commandBusSpy,
            $eventBusSpy,
            $tasksCollection,
        );

        $this->expectException(TaskNotFound::class);

        $handler->__invoke(
            new RerunJob($originalJobId, $newJobId)
        );
    }


    public function testJobWithTaskWillBeScheduled(): void
    {
        $clock = FrozenClock::fromUTC();
        $originalJobId = new JobId('1');
        $newJobId = new JobId('2');
        $taskId = new TaskId('');
        $jobsCollection = $this->createMock(JobsCollection::class);
        $projectId = new ProjectId('');
        $jobWithTask = new Job(
            $originalJobId,
            $projectId,
            'title',
            ['command'],
            $clock,
            null,
            $taskId,
        );
        $jobsCollection->expects(self::once())
            ->method('get')
            ->willReturn($jobWithTask);

        $task = new Task(
            $taskId,
            $projectId,
            'title',
            ['command'],
            true,
        );
        $tasksCollection = $this->createMock(TasksCollection::class);
        $tasksCollection->expects(self::once())
            ->method('get')
            ->willReturn($task);

        $commandBusSpy = $this->createMock(CommandBus::class);
        $commandBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->callback(static function(ExecuteJob $command) use ($newJobId, $task): bool {
                self::assertSame($task->mergeAutomatically, $command->mergeAutomatically);
                self::assertSame($command->jobId, $newJobId);

                return true;
            }));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->callback(static function(JobScheduled $event) use ($newJobId): bool {
                return $event->jobId === $newJobId;
            }));

        $handler = new RerunJobHandler(
            $jobsCollection,
            $clock,
            $commandBusSpy,
            $eventBusSpy,
            $tasksCollection,
        );

        $handler->__invoke(
            new RerunJob($originalJobId, $newJobId)
        );
    }
}
