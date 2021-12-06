<?php

declare(strict_types=1);

namespace PHPMate\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Lcobucci\Clock\FrozenClock;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\GitProvider\Value\MergeRequest;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\Process\Value\ProcessResult;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Infrastructure\Cookbook\StaticRecipesCollection;

final class DataFixtures extends Fixture
{
    public const PROJECT_ID = '5cc4892e-ad6c-4e7b-b861-f73c7ddbab28';
    public const TASK_ID = '57fa7f60-8992-4060-ba05-f617d32f053e';
    public const JOB_1_ID = '6bcede0c-21de-4472-b6a4-853d287ed16b';
    public const JOB_2_ID = '7a779f13-e3ce-4dc4-bf53-04f06096b70f';
    public const JOB_3_ID = '892e7e2d-6073-474f-9d4b-75dda88b352c';
    public const REMOTE_REPOSITORY_URI = 'https://gitlab.com/phpmate/phpmate.git';
    public const PROJECT_NAME = 'phpmate/phpmate';

    public function __construct(
        private RecipesCollection $recipesCollection
    ) {}


    public function load(ObjectManager $manager): void
    {
        $remoteGitRepository = self::createRemoteGitRepository();

        $projectId = new ProjectId(self::PROJECT_ID);
        $project = new Project($projectId, $remoteGitRepository);
        $project->enableRecipe(RecipeName::UNUSED_PRIVATE_METHODS());
        $project->enableRecipe(RecipeName::TYPED_PROPERTIES());

        $manager->persist($project);

        $taskId = new TaskId(self::TASK_ID);
        $task = new Task(
            $taskId,
            $projectId,
            'task',
            ['command1', 'command2']
        );

        $manager->persist($task);

        $mergeRequest = new MergeRequest('https://phpmate.io');

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

        $job2Clock = new FrozenClock(new \DateTimeImmutable('2021-01-01 13:00:00'));
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

        $recipe = $this->recipesCollection->get(RecipeName::UNUSED_PRIVATE_METHODS());
        $job3Clock = new FrozenClock(new \DateTimeImmutable('2021-01-01 14:00:00'));
        $job3Id = new JobId(self::JOB_3_ID);
        $job3 = Job::scheduleFromRecipe(
            $job3Id,
            $projectId,
            $recipe,
            $job3Clock,
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
