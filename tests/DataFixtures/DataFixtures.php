<?php

declare(strict_types=1);

namespace PHPMate\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

final class DataFixtures extends Fixture
{
    private const PROJECT_ID = '5cc4892e-ad6c-4e7b-b861-f73c7ddbab28';
    private const TASK_ID = '57fa7f60-8992-4060-ba05-f617d32f053e';
    public const JOB_1_ID = '6bcede0c-21de-4472-b6a4-853d287ed16b';
    public const JOB_2_ID = '7a779f13-e3ce-4dc4-bf53-04f06096b70f';


    public function __construct(
       private Clock $clock,
    ) {}


    public function load(ObjectManager $manager): void
    {
        $projectId = new ProjectId(self::PROJECT_ID);

        $project = new Project(
            $projectId,
            new RemoteGitRepository(
                'https://gitlab.com/phpmate-dogfood/rector.git',
                GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
            )
        );

        $manager->persist($project);

        $taskId = new TaskId(self::TASK_ID);
        $task = new Task(
            $taskId,
            $projectId,
            'task',
            ['command']
        );

        $manager->persist($task);

        // TODO: fix order job1 vs job2 in database (frozen clock)
        $job1Id = new JobId(self::JOB_1_ID);
        $job1 = new Job(
            $job1Id,
            $projectId,
            $taskId,
            $task->name,
            $this->clock,
            $task->commands
        );

        $job1->start($this->clock);
        $job1->succeeds($this->clock);

        $manager->persist($job1);

        // TODO: fix order job1 vs job2 in database (frozen clock)
        $job2Id = new JobId(self::JOB_2_ID);
        $job2 = new Job(
            $job2Id,
            $projectId,
            $taskId,
            $task->name,
            $this->clock,
            $task->commands
        );

        $job2->start($this->clock);
        $job2->fails($this->clock);

        $manager->persist($job2);

        $manager->flush();
    }
}
