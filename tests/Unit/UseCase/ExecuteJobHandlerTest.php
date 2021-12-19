<?php

declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\Job\Exception\JobExecutionFailed;
use PHPMate\Domain\Job\Exception\JobNotFound;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\PhpApplication\BuildApplication;
use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPMate\Domain\PhpApplication\Value\LocalApplication;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Tools\Composer\Exception\ComposerCommandFailed;
use PHPMate\Domain\Tools\Git\Exception\GitCommandFailed;
use PHPMate\Domain\Tools\Git\Git;
use PHPMate\UseCase\ExecuteJob;
use PHPMate\UseCase\ExecuteJobHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ExecuteJobHandlerTest extends TestCase
{
    public function testNotFoundJobWillThrowException(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $jobsCollection = $this->createMock(JobsCollection::class);
        $jobsCollection->expects(self::once())
            ->method('get')
            ->willThrowException(new JobNotFound());

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $this->createMock(ProjectsCollection::class),
            $this->createMock(PrepareApplicationGitRepository::class),
            $this->createMock(BuildApplication::class),
            $this->createMock(Git::class),
            $this->createMock(GitProvider::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
        );

        $this->expectException(JobNotFound::class);

        $handler->__invoke($command);
    }


    public function testMissingProjectWillCancelJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createJobMock($jobId);
        $job->expects(self::once())
            ->method('cancel');

        $jobsCollection = $this->createMock(JobsCollection::class);
        $jobsCollection->method('get')
            ->willReturn($job);
        $jobsCollection->expects(self::once())
            ->method('save');

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->expects(self::once())
            ->method('get')
            ->willThrowException(new ProjectNotFound());

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $this->createMock(PrepareApplicationGitRepository::class),
            $this->createMock(BuildApplication::class),
            $this->createMock(Git::class),
            $this->createMock(GitProvider::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
        );

        $handler->__invoke($command);
    }


    public function testFailedPreparingGitRepositoryWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createJobMock($jobId);
        $job->expects(self::once())
            ->method('fails');

        $jobsCollection = $this->createMock(JobsCollection::class);
        $jobsCollection->method('get')
            ->willReturn($job);
        $jobsCollection->expects(self::exactly(2))
            ->method('save');

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->method('get')
            ->willReturn($this->createProjectMock());

        $prepareApplicationGitRepository = $this->createMock(PrepareApplicationGitRepository::class);
        $prepareApplicationGitRepository->expects(self::once())
            ->method('prepare')
            ->willThrowException(new GitCommandFailed());

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $this->createMock(BuildApplication::class),
            $this->createMock(Git::class),
            $this->createMock(GitProvider::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedBuildingApplicationWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createJobMock($jobId);
        $job->expects(self::once())
            ->method('fails');

        $jobsCollection = $this->createMock(JobsCollection::class);
        $jobsCollection->method('get')
            ->willReturn($job);
        $jobsCollection->expects(self::exactly(2))
            ->method('save');

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->method('get')
            ->willReturn($this->createProjectMock());

        $prepareApplicationGitRepository = $this->createMock(PrepareApplicationGitRepository::class);
        $prepareApplicationGitRepository->method('prepare')
            ->willReturn(new LocalApplication('', '', ''));

        $buildApplication = $this->createMock(BuildApplication::class);
        $buildApplication->expects(self::once())
            ->method('build')
            ->willThrowException(new ComposerCommandFailed());

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(Git::class),
            $this->createMock(GitProvider::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedRunningCommandWillFailJob(): void
    {
    }


    public function testFailedOpeningMergeRequestWillFailJob(): void
    {
    }


    public function testJobWillSucceed(): void
    {
    }


    /**
     * @return Job&MockObject
     */
    private function createJobMock(JobId $jobId): MockObject
    {
        return $this->getMockBuilder(Job::class)
            ->setConstructorArgs([
                $jobId,
                new ProjectId(''),
                'Title',
                ['command'],
                $this->createMock(Clock::class),
            ])
            ->getMock();
    }


    /**
     * @return Project&MockObject
     */
    private function createProjectMock(): MockObject
    {
        return $this->getMockBuilder(Project::class)
            ->setConstructorArgs([
                new ProjectId(''),
                new RemoteGitRepository('https://gitlab.com/phpmate/phpmate.git', GitRepositoryAuthentication::fromPersonalAccessToken('PAT')),
            ])
            ->getMock();
    }
}
