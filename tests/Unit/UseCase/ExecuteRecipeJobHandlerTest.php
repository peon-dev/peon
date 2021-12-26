<?php

declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\MergeRequest;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\Job\Exception\JobExecutionFailed;
use PHPMate\Domain\Job\Exception\JobNotFound;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Job\RunJobRecipe;
use PHPMate\Domain\Job\UpdateMergeRequest;
use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\PhpApplication\BuildApplication;
use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPMate\Domain\PhpApplication\Value\LocalApplication;
use PHPMate\Domain\Process\Exception\ProcessFailed;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Tools\Composer\Exception\ComposerCommandFailed;
use PHPMate\Domain\Tools\Git\Exception\GitCommandFailed;
use PHPMate\UseCase\ExecuteRecipeJob;
use PHPMate\UseCase\ExecuteRecipeJobHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ExecuteRecipeJobHandlerTest extends TestCase
{
    public function testNotFoundJobWillThrowException(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteRecipeJob($jobId);

        $jobsCollection = $this->createMock(JobsCollection::class);
        $jobsCollection->expects(self::once())
            ->method('get')
            ->willThrowException(new JobNotFound());

        $handler = new ExecuteRecipeJobHandler(
            $jobsCollection,
            $this->createMock(ProjectsCollection::class),
            $this->createMock(PrepareApplicationGitRepository::class),
            $this->createMock(BuildApplication::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
        );

        $this->expectException(JobNotFound::class);

        $handler->__invoke($command);
    }


    public function testMissingProjectWillCancelJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteRecipeJob($jobId);

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

        $handler = new ExecuteRecipeJobHandler(
            $jobsCollection,
            $projectsCollection,
            $this->createMock(PrepareApplicationGitRepository::class),
            $this->createMock(BuildApplication::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
        );

        $handler->__invoke($command);
    }


    public function testFailedPreparingGitRepositoryWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteRecipeJob($jobId);

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

        $handler = new ExecuteRecipeJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $this->createMock(BuildApplication::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedBuildingApplicationWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteRecipeJob($jobId);

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

        $handler = new ExecuteRecipeJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedRunningRecipeWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteRecipeJob($jobId);

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

        $runJobRecipe = $this->createMock(RunJobRecipe::class);
        $runJobRecipe->expects(self::once())
            ->method('run')
            ->willThrowException(new ProcessFailed());

        $handler = new ExecuteRecipeJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $runJobRecipe,
            $this->createMock(UpdateMergeRequest::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedOpeningMergeRequestWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteRecipeJob($jobId);

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
        $runJobRecipe = $this->createMock(RunJobRecipe::class);

        $updateMergeRequest = $this->createMock(UpdateMergeRequest::class);
        $updateMergeRequest->expects(self::once())
            ->method('update')
            ->willThrowException(new GitProviderCommunicationFailed());

        $handler = new ExecuteRecipeJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $runJobRecipe,
            $updateMergeRequest,
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testJobWillSucceed(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteRecipeJob($jobId);

        $job = $this->createJobMock($jobId);
        $job->expects(self::once())
            ->method('succeeds');

        $jobsCollection = $this->createMock(JobsCollection::class);
        $jobsCollection->method('get')
            ->willReturn($job);
        $jobsCollection->expects(self::exactly(2))
            ->method('save');

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->expects(self::once())
            ->method('get')
            ->willReturn($this->createProjectMock());

        $prepareApplicationGitRepository = $this->createMock(PrepareApplicationGitRepository::class);
        $prepareApplicationGitRepository->expects(self::once())
            ->method('prepare')
            ->willReturn(new LocalApplication('', '', ''));

        $buildApplication = $this->createMock(BuildApplication::class);
        $buildApplication->expects(self::once())
            ->method('build');

        $runJobRecipe = $this->createMock(RunJobRecipe::class);
        $runJobRecipe->expects(self::once())
            ->method('run');

        $updateMergeRequest = $this->createMock(UpdateMergeRequest::class);
        $updateMergeRequest->expects(self::once())
            ->method('update')
            ->willReturn(new MergeRequest('url'));

        $handler = new ExecuteRecipeJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $runJobRecipe,
            $updateMergeRequest,
        );

        $handler->__invoke($command);
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
                RecipeName::TYPED_PROPERTIES,
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
