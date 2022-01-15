<?php

declare(strict_types=1);

namespace Peon\Tests\DataFixtures;

use Cron\CronExpression;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Lcobucci\Clock\FrozenClock;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Infrastructure\Cookbook\StaticRecipesCollection;

final class DataFixtures extends Fixture
{
    public const PROJECT_1_ID = '5cc4892e-ad6c-4e7b-b861-f73c7ddbab28';
    public const PROJECT_2_ID = '4a05eef8-4127-472f-915a-c69eb59341b1';
    public const TASK_ID = '57fa7f60-8992-4060-ba05-f617d32f053e';
    public const TASK_SCHEDULE = '8 * * * *';
    public const JOB_1_ID = '6bcede0c-21de-4472-b6a4-853d287ed16b';
    public const JOB_2_ID = '7a779f13-e3ce-4dc4-bf53-04f06096b70f';
    public const JOB_2_DATETIME = '2021-01-01 13:00:00';
    public const JOB_3_ID = '892e7e2d-6073-474f-9d4b-75dda88b352c';
    public const JOB_3_DATETIME = '2021-01-01 14:00:00';
    public const REMOTE_REPOSITORY_URI = 'https://gitlab.com/peon/peon.git';
    public const PROJECT_NAME = 'peon/peon';

    public function __construct(
        private RecipesCollection $recipesCollection
    ) {}


    public function load(ObjectManager $manager): void
    {
        $remoteGitRepository = self::createRemoteGitRepository();

        $projectId = new ProjectId(self::PROJECT_1_ID);
        $project = new Project($projectId, $remoteGitRepository);
        $project->enableRecipe(RecipeName::UNUSED_PRIVATE_METHODS);
        $project->enableRecipe(RecipeName::TYPED_PROPERTIES, 'abcde');

        $manager->persist($project);

        $emptyProjectId = new ProjectId(self::PROJECT_2_ID);
        $emptyProject = new Project($emptyProjectId, $remoteGitRepository);

        $manager->persist($emptyProject);

        $taskId = new TaskId(self::TASK_ID);
        $task = new Task(
            $taskId,
            $projectId,
            'task',
            ['command1', 'command2']
        );
        $task->changeSchedule(new CronExpression(self::TASK_SCHEDULE));

        $manager->persist($task);

        $mergeRequest = new MergeRequest('https://peon.dev');

        $job1Clock = new FrozenClock(new \DateTimeImmutable('2021-01-01 12:00:00'));
        $job1Id = new JobId(self::JOB_1_ID);
        $job1 = Job::scheduleFromTask(
            $job1Id,
            $projectId,
            $task,
            $job1Clock,
        );

        $job1->start($job1Clock);
        $job1->succeeds($job1Clock, $mergeRequest);

        foreach ($task->commands as $command) {
            $job1->addProcessResult(
                new ProcessResult(
                    $command,
                    0,
                    'output',
                    1
                )
            );
        }

        $manager->persist($job1);

        $job2Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_2_DATETIME));
        $job2Id = new JobId(self::JOB_2_ID);
        $job2 = Job::scheduleFromTask(
            $job2Id,
            $projectId,
            $task,
            $job2Clock,
        );

        $job2->start($job2Clock);
        $job2->fails($job2Clock, $mergeRequest);

        foreach ($task->commands as $command) {
            $job2->addProcessResult(
                new ProcessResult(
                    $command,
                    1,
                    'output',
                    1
                )
            );
        }

        $manager->persist($job2);

        $recipe = $this->recipesCollection->get(RecipeName::UNUSED_PRIVATE_METHODS);
        $job3Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_3_DATETIME));
        $job3Id = new JobId(self::JOB_3_ID);
        $job3 = Job::scheduleFromRecipe(
            $job3Id,
            $projectId,
            $recipe,
            $job3Clock,
            null,
        );

        $job3->start($job3Clock);
        $job3->succeeds($job3Clock, $mergeRequest);

        $manager->persist($job3);

        $manager->flush();
    }


    public static function createRemoteGitRepository(): RemoteGitRepository
    {
        return new RemoteGitRepository(
            self::REMOTE_REPOSITORY_URI,
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );
    }
}
