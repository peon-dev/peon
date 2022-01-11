<?php

declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Job\Event\JobStatusChanged;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\RunJobCommands;
use Peon\Domain\Job\RunJobRecipe;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\PhpApplication\BuildApplication;
use Peon\Domain\PhpApplication\PrepareApplicationGitRepository;
use Peon\Domain\PhpApplication\Value\LocalApplication;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Tools\Composer\Exception\ComposerCommandFailed;
use Peon\Domain\Tools\Git\Exception\GitCommandFailed;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\ExecuteJob;
use Peon\UseCase\ExecuteJobHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
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
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobCommands::class),
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
            $this->createMock(EventBus::class),
        );

        $this->expectException(JobNotFound::class);

        $handler->__invoke($command);
    }


    public function testMissingProjectWillCancelJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createTaskJobMock($jobId);
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

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $this->createMock(PrepareApplicationGitRepository::class),
            $this->createMock(BuildApplication::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobCommands::class),
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
        );

        $handler->__invoke($command);
    }


    public function testFailedPreparingGitRepositoryWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createTaskJobMock($jobId);
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

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $this->createMock(BuildApplication::class),
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobCommands::class),
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedBuildingApplicationWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createTaskJobMock($jobId);
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

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobCommands::class),
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedRunningCommandWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createTaskJobMock($jobId);
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

        $runJobCommands = $this->createMock(RunJobCommands::class);
        $runJobCommands->expects(self::once())
            ->method('run')
            ->willThrowException(new ProcessFailed());

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $runJobCommands,
            $this->createMock(RunJobRecipe::class),
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedRunningRecipeWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createRecipeJobMock($jobId);
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

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobCommands::class),
            $runJobRecipe,
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedOpeningMergeRequestWillFailJob(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createTaskJobMock($jobId);
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
        $runJobCommands = $this->createMock(RunJobCommands::class);

        $updateMergeRequest = $this->createMock(UpdateMergeRequest::class);
        $updateMergeRequest->expects(self::once())
            ->method('update')
            ->willThrowException(new GitProviderCommunicationFailed());

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $runJobCommands,
            $this->createMock(RunJobRecipe::class),
            $updateMergeRequest,
            $eventBusSpy,
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testTaskJobWillSucceed(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createTaskJobMock($jobId);
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

        $runJobCommands = $this->createMock(RunJobCommands::class);
        $runJobCommands->expects(self::once())
            ->method('run');

        $updateMergeRequest = $this->createMock(UpdateMergeRequest::class);
        $updateMergeRequest->expects(self::once())
            ->method('update')
            ->willReturn(new MergeRequest('url'));

        $runJobRecipe = $this->createMock(RunJobRecipe::class);
        $runJobRecipe->expects(self::never())
            ->method('run');

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $runJobCommands,
            $runJobRecipe,
            $updateMergeRequest,
            $eventBusSpy,
        );

        $handler->__invoke($command);
    }


    public function testRecipeJobWillSucceed(): void
    {
        $jobId = new JobId('');
        $command = new ExecuteJob($jobId);

        $job = $this->createRecipeJobMock($jobId);
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

        $runJobCommands = $this->createMock(RunJobCommands::class);
        $runJobCommands->expects(self::never())
            ->method('run');

        $updateMergeRequest = $this->createMock(UpdateMergeRequest::class);
        $updateMergeRequest->expects(self::once())
            ->method('update')
            ->willReturn(new MergeRequest('url'));

        $runJobRecipe = $this->createMock(RunJobRecipe::class);
        $runJobRecipe->expects(self::once())
            ->method('run');

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(ProcessLogger::class),
            $this->createMock(Clock::class),
            $this->createMock(RunJobCommands::class),
            $runJobRecipe,
            $updateMergeRequest,
            $eventBusSpy,
        );

        $handler->__invoke($command);
    }


    /**
     * @return Job&MockObject
     */
    private function createTaskJobMock(JobId $jobId): MockObject
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
     * @return Job&MockObject
     */
    private function createRecipeJobMock(JobId $jobId): MockObject
    {
        return $this->getMockBuilder(Job::class)
            ->setConstructorArgs([
                $jobId,
                new ProjectId(''),
                'Title',
                null,
                $this->createMock(Clock::class),
                new EnabledRecipe(RecipeName::TYPED_PROPERTIES, 'abc'),
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
                new RemoteGitRepository('https://gitlab.com/peon/peon.git', GitRepositoryAuthentication::fromPersonalAccessToken('PAT')),
            ])
            ->getMock();
    }
}
