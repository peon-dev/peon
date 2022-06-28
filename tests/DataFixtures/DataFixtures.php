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
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\Process;
use Peon\Domain\Process\RunProcess;
use Peon\Domain\Process\Value\ProcessId;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\User\HashPlainTextPassword;
use Peon\Domain\User\User;
use Peon\Domain\User\Value\UserId;
use Peon\Domain\Worker\Value\WorkerId;
use Peon\Domain\Worker\WorkerStatus;
use Ramsey\Uuid\Uuid;

final class DataFixtures extends Fixture
{
    public const TASK_SCHEDULE = '8 * * * *';
    public const JOB_2_DATETIME = '2021-01-01 13:00:00';
    public const JOB_3_DATETIME = '2021-01-01 14:00:00';
    public const JOB_4_DATETIME = '2021-01-01 15:00:00';
    public const JOB_5_DATETIME = '2021-01-01 16:00:00';
    public const REMOTE_REPOSITORY_URI = 'https://gitlab.com/peon/peon.git';
    public const PROJECT_NAME = 'peon/peon';
    public const USER_PASSWORD = '12345';
    public const WORKER_LAST_SEEN_AT = '2021-01-01 12:00:00';

    public const USER_1_ID = 'a26d6d92-eb4d-11ec-ac9e-1266a710edb4';
    public const USER_1_USERNAME = 'peon-1';
    public const USER_1_PROJECT_1_ID = '5cc4892e-ad6c-4e7b-b861-f73c7ddbab28';
    public const USER_1_PROJECT_2_ID = '4a05eef8-4127-472f-915a-c69eb59341b1';
    public const USER_1_TASK_ID = '57fa7f60-8992-4060-ba05-f617d32f053e';
    public const USER_1_JOB_1_ID = '6bcede0c-21de-4472-b6a4-853d287ed16b';
    public const USER_1_JOB_2_ID = '7a779f13-e3ce-4dc4-bf53-04f06096b70f';
    public const USER_1_JOB_3_ID = '892e7e2d-6073-474f-9d4b-75dda88b352c';
    public const USER_1_JOB_4_ID = 'a92e7e2d-6073-474f-9d4b-75dda88b352c';
    public const USER_1_JOB_5_ID = '73a3909e-f63e-11ec-a727-1266a710edb4';

    public const USER_2_ID = 'e6b281f4-eb66-11ec-8907-1266a710edb4';
    public const USER_2_USERNAME = 'peon-2';
    public const USER_2_PROJECT_1_ID = '3ed46136-eb67-11ec-a60e-1266a710edb4';
    public const USER_2_PROJECT_2_ID = '419dddd4-eb67-11ec-9f9f-1266a710edb4';
    public const USER_2_TASK_ID = '4bcc7252-eb67-11ec-9752-1266a710edb4';
    public const USER_2_JOB_1_ID = '6b03cf62-eb67-11ec-b9ca-1266a710edb4';
    public const USER_2_JOB_2_ID = '6d7201b0-eb67-11ec-a82c-1266a710edb4';
    public const USER_2_JOB_3_ID = '705ed7c2-eb67-11ec-8377-1266a710edb4';
    public const USER_2_JOB_4_ID = '72e331b4-eb67-11ec-a1f8-1266a710edb4';
    public const USER_2_JOB_5_ID = '77c6a260-f63e-11ec-ad3a-1266a710edb4';

    public function __construct(
        private RecipesCollection $recipesCollection,
        private HashPlainTextPassword $hashPlainTextPassword,
    ) {}


    public function load(ObjectManager $manager): void
    {
        $this->loadUser1Data($manager);
        $this->loadUser2Data($manager);

        $workerStatus = new WorkerStatus(
            new WorkerId('fixture'),
            new FrozenClock(new \DateTimeImmutable(self::WORKER_LAST_SEEN_AT)),
        );
        $manager->persist($workerStatus);

        $manager->flush();
    }


    public static function createRemoteGitRepository(): RemoteGitRepository
    {
        return new RemoteGitRepository(
            self::REMOTE_REPOSITORY_URI,
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );
    }

    private function createProcess(JobId $job, int $sequence, string $command): Process
    {
        $process = new Process(
            new ProcessId(Uuid::uuid4()->toString()),
            $job,
            $sequence,
            $command,
            60
        );

        $process->runInDirectory('/', new class implements RunProcess {
            public function inDirectory(string|null $workingDirectory, string $command, int $timeoutSeconds): ProcessResult
            {
                return new ProcessResult(
                    0,
                    1.5,
                    'output',
                );
            }
        });

        return $process;
    }


    private function loadUser1Data(ObjectManager $manager): void
    {
        $userId = new UserId(self::USER_1_ID);

        $user = new User(
            $userId,
            self::USER_1_USERNAME,
            $this->hashPlainTextPassword->hash(self::USER_PASSWORD),
        );
        $manager->persist($user);

        $remoteGitRepository = self::createRemoteGitRepository();

        $projectId = new ProjectId(self::USER_1_PROJECT_1_ID);
        $project = new Project($projectId, $remoteGitRepository, $userId);
        $project->enableRecipe(RecipeName::UNUSED_PRIVATE_METHODS);
        $project->enableRecipe(RecipeName::TYPED_PROPERTIES, 'abcde');

        $manager->persist($project);

        $emptyProjectId = new ProjectId(self::USER_1_PROJECT_2_ID);
        $emptyProject = new Project($emptyProjectId, $remoteGitRepository, $userId);

        $manager->persist($emptyProject);

        $taskId = new TaskId(self::USER_1_TASK_ID);
        $task = new Task(
            $taskId,
            $projectId,
            'task',
            ['command1', 'command2'],
            false,
        );
        $task->changeSchedule(new CronExpression(self::TASK_SCHEDULE));

        $manager->persist($task);

        $job1Clock = new FrozenClock(new \DateTimeImmutable('2021-01-01 12:00:00'));
        $job1Id = new JobId(self::USER_1_JOB_1_ID);
        $job1 = Job::scheduleFromTask(
            $job1Id,
            $projectId,
            $task,
            $job1Clock,
        );

        $job1->start($job1Clock);
        $job1->succeeds($job1Clock, new MergeRequest('1', 'https://peon.dev'));

        foreach ($task->commands as $sequence => $command) {
            $process = $this->createProcess($job1Id, $sequence, $command);
            $manager->persist($process);
        }

        $manager->persist($job1);

        $job2Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_2_DATETIME));
        $job2Id = new JobId(self::USER_1_JOB_2_ID);
        $job2 = Job::scheduleFromTask(
            $job2Id,
            $projectId,
            $task,
            $job2Clock,
        );

        $job2->start($job2Clock);
        $job2->fails($job2Clock, new MergeRequest('2', 'https://peon.dev'));

        foreach ($task->commands as $sequence => $command) {
            $process = $this->createProcess($job2Id, $sequence, $command);
            $manager->persist($process);
        }

        $manager->persist($job2);

        $recipe = $this->recipesCollection->get(RecipeName::UNUSED_PRIVATE_METHODS);
        $job3Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_3_DATETIME));
        $job3Id = new JobId(self::USER_1_JOB_3_ID);
        $job3 = Job::scheduleFromRecipe(
            $job3Id,
            $projectId,
            $job3Clock,
            $recipe->title,
            EnabledRecipe::withoutConfiguration($recipe->name, null),
        );

        $job3->start($job3Clock);
        $job3->succeeds($job3Clock);

        $manager->persist($job3);

        $recipe = $this->recipesCollection->get(RecipeName::UNUSED_PRIVATE_METHODS);
        $job4Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_4_DATETIME));
        $job4Id = new JobId(self::USER_1_JOB_4_ID);
        $job4 = Job::scheduleFromRecipe(
            $job4Id,
            $projectId,
            $job4Clock,
            $recipe->title,
            EnabledRecipe::withoutConfiguration($recipe->name, null),
        );

        $job4->start($job4Clock);

        $manager->persist($job4);

        $recipe = $this->recipesCollection->get(RecipeName::VOID_RETURN);
        $job5Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_5_DATETIME));
        $job5Id = new JobId(self::USER_1_JOB_5_ID);
        $job5 = Job::scheduleFromRecipe(
            $job5Id,
            $projectId,
            $job5Clock,
            $recipe->title,
            EnabledRecipe::withoutConfiguration($recipe->name, null),
        );

        $manager->persist($job5);
    }


    private function loadUser2Data(ObjectManager $manager): void
    {
        $userId = new UserId(self::USER_2_ID);

        $user = new User(
            $userId,
            self::USER_2_USERNAME,
            $this->hashPlainTextPassword->hash(self::USER_PASSWORD),
        );
        $manager->persist($user);

        $remoteGitRepository = self::createRemoteGitRepository();

        $projectId = new ProjectId(self::USER_2_PROJECT_1_ID);
        $project = new Project($projectId, $remoteGitRepository, $userId);
        $project->enableRecipe(RecipeName::UNUSED_PRIVATE_METHODS);
        $project->enableRecipe(RecipeName::TYPED_PROPERTIES, 'abcde');

        $manager->persist($project);

        $emptyProjectId = new ProjectId(self::USER_2_PROJECT_2_ID);
        $emptyProject = new Project($emptyProjectId, $remoteGitRepository, $userId);

        $manager->persist($emptyProject);

        $taskId = new TaskId(self::USER_2_TASK_ID);
        $task = new Task(
            $taskId,
            $projectId,
            'task',
            ['command1', 'command2'],
            false,
        );
        $task->changeSchedule(new CronExpression(self::TASK_SCHEDULE));

        $manager->persist($task);

        $job1Clock = new FrozenClock(new \DateTimeImmutable('2021-01-01 12:00:00'));
        $job1Id = new JobId(self::USER_2_JOB_1_ID);
        $job1 = Job::scheduleFromTask(
            $job1Id,
            $projectId,
            $task,
            $job1Clock,
        );

        $job1->start($job1Clock);
        $job1->succeeds($job1Clock, new MergeRequest('1', 'https://peon.dev'));

        foreach ($task->commands as $sequence => $command) {
            $process = $this->createProcess($job1Id, $sequence, $command);
            $manager->persist($process);
        }

        $manager->persist($job1);

        $job2Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_2_DATETIME));
        $job2Id = new JobId(self::USER_2_JOB_2_ID);
        $job2 = Job::scheduleFromTask(
            $job2Id,
            $projectId,
            $task,
            $job2Clock,
        );

        $job2->start($job2Clock);
        $job2->fails($job2Clock, new MergeRequest('2', 'https://peon.dev'));

        foreach ($task->commands as $sequence => $command) {
            $process = $this->createProcess($job2Id, $sequence, $command);
            $manager->persist($process);
        }

        $manager->persist($job2);

        $recipe = $this->recipesCollection->get(RecipeName::UNUSED_PRIVATE_METHODS);
        $job3Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_3_DATETIME));
        $job3Id = new JobId(self::USER_2_JOB_3_ID);
        $job3 = Job::scheduleFromRecipe(
            $job3Id,
            $projectId,
            $job3Clock,
            $recipe->title,
            EnabledRecipe::withoutConfiguration($recipe->name, null),
        );

        $job3->start($job3Clock);
        $job3->succeeds($job3Clock);

        $manager->persist($job3);

        $recipe = $this->recipesCollection->get(RecipeName::UNUSED_PRIVATE_METHODS);
        $job4Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_4_DATETIME));
        $job4Id = new JobId(self::USER_2_JOB_4_ID);
        $job4 = Job::scheduleFromRecipe(
            $job4Id,
            $projectId,
            $job4Clock,
            $recipe->title,
            EnabledRecipe::withoutConfiguration($recipe->name, null),
        );

        $job4->start($job4Clock);

        $manager->persist($job4);

        $recipe = $this->recipesCollection->get(RecipeName::VOID_RETURN);
        $job5Clock = new FrozenClock(new \DateTimeImmutable(self::JOB_5_DATETIME));
        $job5Id = new JobId(self::USER_2_JOB_5_ID);
        $job5 = Job::scheduleFromRecipe(
            $job5Id,
            $projectId,
            $job5Clock,
            $recipe->title,
            EnabledRecipe::withoutConfiguration($recipe->name, null),
        );

        $manager->persist($job5);
    }
}
