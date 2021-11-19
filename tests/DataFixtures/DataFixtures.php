<?php

declare(strict_types=1);

namespace PHPMate\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Lcobucci\Clock\FrozenClock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

final class DataFixtures extends Fixture
{
    public const PROJECT_ID = '5cc4892e-ad6c-4e7b-b861-f73c7ddbab28';
    public const TASK_ID = '57fa7f60-8992-4060-ba05-f617d32f053e';
    public const JOB_1_ID = '6bcede0c-21de-4472-b6a4-853d287ed16b';
    public const JOB_2_ID = '7a779f13-e3ce-4dc4-bf53-04f06096b70f';


    public function load(ObjectManager $manager): void
    {
        $projectId = new ProjectId(self::PROJECT_ID);

        // TODO: consider using some kind of factory, it is used on way too many places already
        $remoteGitRepository = new RemoteGitRepository(
            'https://gitlab.com/phpmate-dogfood/rector.git',
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );

        $project = new Project($projectId, $remoteGitRepository);

        $manager->persist($project);

        $taskId = new TaskId(self::TASK_ID);
        $task = new Task(
            $taskId,
            $projectId,
            'task',
            ['command']
        );

        $manager->persist($task);

        $job1Clock = new FrozenClock(new \DateTimeImmutable('2021-01-01 12:00:00'));
        $job1Id = new JobId(self::JOB_1_ID);
        $job1 = new Job(
            $job1Id,
            $projectId,
            $taskId,
            $task->name,
            $job1Clock,
            $task->commands
        );

        $job1->start($job1Clock);
        $job1->succeeds($job1Clock);


        $job1->addProcessResult(
            new ProcessResult(
                'command1',
                0,
                '',
                1.0
            )
        );

        $job1->addProcessResult(
            new ProcessResult(
                'command2',
                0,
                '',
                1.0
            )
        );

        $manager->persist($job1);


        $job2Clock = new FrozenClock(new \DateTimeImmutable('2021-01-01 13:00:00'));
        $job2Id = new JobId(self::JOB_2_ID);
        $job2 = new Job(
            $job2Id,
            $projectId,
            $taskId,
            $task->name,
            $job2Clock,
            $task->commands
        );

        $job2->start($job2Clock);
        $job2->fails($job2Clock);

        $job2->addProcessResult(
            new ProcessResult(
                'command1',
                0,
                '',
                1.0
            )
        );

        $job2->addProcessResult(
            new ProcessResult(
                'command2',
                1,
                '',
                1.0
            )
        );

        $manager->persist($job2);
        $manager->flush();
    }
}
