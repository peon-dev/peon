<?php

declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Application\DetectApplicationLanguage;
use Peon\Domain\Application\Value\ApplicationLanguage;
use Peon\Domain\Container\DetectContainerImage;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Job\Event\JobStatusChanged;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\GetRecipeCommands;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\PhpApplication\BuildPhpApplication;
use Peon\Domain\Application\PrepareApplicationGitRepository;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\Tests\DataFixtures\TestDataFactory;
use Peon\UseCase\ExecuteJob;
use Peon\UseCase\ExecuteJobHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ExecuteJobHandlerTest extends TestCase
{
    public function testNotFoundJobWillThrowException(): void
    {
        $temporaryApplication = TestDataFactory::createTemporaryApplication();
        $command = new ExecuteJob($temporaryApplication->jobId, false);

        $jobsCollection = $this->createMock(JobsCollection::class);
        $jobsCollection->expects(self::once())
            ->method('get')
            ->willThrowException(new JobNotFound());

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $this->createMock(ProjectsCollection::class),
            $this->createMock(PrepareApplicationGitRepository::class),
            $this->createMock(BuildPhpApplication::class),
            $this->createMock(Clock::class),
            $this->createMock(UpdateMergeRequest::class),
            $this->createMock(EventBus::class),
            $this->createMock(ExecuteCommand::class),
            $this->createMock(DetectApplicationLanguage::class),
            $this->createMock(GetRecipeCommands::class),
            $this->createMock(DetectContainerImage::class),
        );

        $this->expectException(JobNotFound::class);

        $handler->__invoke($command);
    }


    public function testMissingProjectWillCancelJob(): void
    {
        $temporaryApplication = TestDataFactory::createTemporaryApplication();
        $command = new ExecuteJob($temporaryApplication->jobId, false);

        $job = $this->createTaskJobMock($temporaryApplication->jobId);
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
            $this->createMock(BuildPhpApplication::class),
            $this->createMock(Clock::class),
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
            $this->createMock(ExecuteCommand::class),
            $this->createMock(DetectApplicationLanguage::class),
            $this->createMock(GetRecipeCommands::class),
            $this->createMock(DetectContainerImage::class),
        );

        $handler->__invoke($command);
    }


    public function testFailedPreparingGitRepositoryWillFailJob(): void
    {
        $temporaryApplication = TestDataFactory::createTemporaryApplication();
        $command = new ExecuteJob($temporaryApplication->jobId, false);

        $job = $this->createTaskJobMock($temporaryApplication->jobId);
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
            ->method('forRemoteRepository')
            ->willThrowException(new ProcessFailed(new ProcessResult(1, 0, '')));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $this->createMock(BuildPhpApplication::class),
            $this->createMock(Clock::class),
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
            $this->createMock(ExecuteCommand::class),
            $this->createMock(DetectApplicationLanguage::class),
            $this->createMock(GetRecipeCommands::class),
            $this->createMock(DetectContainerImage::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedBuildingApplicationWillFailJob(): void
    {
        $temporaryApplication = TestDataFactory::createTemporaryApplication();
        $command = new ExecuteJob($temporaryApplication->jobId, false);

        $job = $this->createTaskJobMock($temporaryApplication->jobId);
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
        $prepareApplicationGitRepository->method('forRemoteRepository')
            ->willReturn($temporaryApplication->gitRepository);

        $buildApplication = $this->createMock(BuildPhpApplication::class);
        $buildApplication->expects(self::once())
            ->method('build')
            ->willThrowException(new ProcessFailed(new ProcessResult(1, 0, '')));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(Clock::class),
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
            $this->createMock(ExecuteCommand::class),
            $this->createDetectApplicationLanguage(),
            $this->createMock(GetRecipeCommands::class),
            $this->createMock(DetectContainerImage::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    /**
     * @return \Generator<array{Job&MockObject}>
     */
    public function provideRecipeAndTaskJobs(): \Generator
    {
        $jobId = TestDataFactory::createTemporaryApplication()->jobId;

        yield [$this->createTaskJobMock($jobId)];
        yield [$this->createRecipeJobMock($jobId)];
    }

    /**
     * @dataProvider provideRecipeAndTaskJobs
     */
    public function testFailedRunningCommandWillFailJob(Job&MockObject $job): void
    {
        $temporaryApplication = TestDataFactory::createTemporaryApplication();
        $command = new ExecuteJob($temporaryApplication->jobId, false);

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
        $prepareApplicationGitRepository->method('forRemoteRepository')
            ->willReturn($temporaryApplication->gitRepository);

        $buildApplication = $this->createMock(BuildPhpApplication::class);

        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inContainer')
            ->willThrowException(new ProcessFailed(new ProcessResult(1, 0, '')));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $getRecipeCommands = $this->createMock(GetRecipeCommands::class);
        $getRecipeCommands->method('forApplication')->willReturn(['command']);

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(Clock::class),
            $this->createMock(UpdateMergeRequest::class),
            $eventBusSpy,
            $executeCommand,
            $this->createDetectApplicationLanguage(),
            $getRecipeCommands,
            $this->createMock(DetectContainerImage::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testFailedOpeningMergeRequestWillFailJob(): void
    {
        $temporaryApplication = TestDataFactory::createTemporaryApplication();
        $command = new ExecuteJob($temporaryApplication->jobId, false);

        $job = $this->createTaskJobMock($temporaryApplication->jobId);
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
        $prepareApplicationGitRepository->method('forRemoteRepository')
            ->willReturn($temporaryApplication->gitRepository);

        $buildApplication = $this->createMock(BuildPhpApplication::class);

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
            $this->createMock(Clock::class),
            $updateMergeRequest,
            $eventBusSpy,
            $this->createMock(ExecuteCommand::class),
            $this->createDetectApplicationLanguage(),
            $this->createMock(GetRecipeCommands::class),
            $this->createMock(DetectContainerImage::class),
        );

        $this->expectException(JobExecutionFailed::class);

        $handler->__invoke($command);
    }


    public function testTaskJobWillSucceed(): void
    {
        $temporaryApplication = TestDataFactory::createTemporaryApplication();
        $command = new ExecuteJob($temporaryApplication->jobId, false);

        $job = $this->createTaskJobMock($temporaryApplication->jobId);
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
            ->method('forRemoteRepository')
            ->willReturn($temporaryApplication->gitRepository);

        $buildApplication = $this->createMock(BuildPhpApplication::class);
        $buildApplication->expects(self::once())
            ->method('build');

        $updateMergeRequest = $this->createMock(UpdateMergeRequest::class);
        $updateMergeRequest->expects(self::once())
            ->method('update')
            ->willReturn(new MergeRequest('id', 'url'));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $detectContainerImage = $this->createMock(DetectContainerImage::class);
        $detectContainerImage->expects(self::once())->method('forLanguage');

        $getRecipeCommands = $this->createMock(GetRecipeCommands::class);
        $getRecipeCommands->expects(self::never())->method('forApplication');

        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::never())->method('inDirectory');
        $executeCommand->expects(self::once())->method('inContainer');

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(Clock::class),
            $updateMergeRequest,
            $eventBusSpy,
            $executeCommand,
            $this->createDetectApplicationLanguage(),
            $getRecipeCommands,
            $detectContainerImage,
        );

        $handler->__invoke($command);
    }


    public function testRecipeJobWillSucceed(): void
    {
        $temporaryApplication = TestDataFactory::createTemporaryApplication();
        $command = new ExecuteJob($temporaryApplication->jobId, false);

        $job = $this->createRecipeJobMock($temporaryApplication->jobId);
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
            ->method('forRemoteRepository')
            ->willReturn($temporaryApplication->gitRepository);

        $buildApplication = $this->createMock(BuildPhpApplication::class);
        $buildApplication->expects(self::once())
            ->method('build');

        $updateMergeRequest = $this->createMock(UpdateMergeRequest::class);
        $updateMergeRequest->expects(self::once())
            ->method('update')
            ->willReturn(new MergeRequest('id', 'url'));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::exactly(2))
            ->method('dispatch')
            ->with(new IsInstanceOf(JobStatusChanged::class));

        $getRecipeCommands = $this->createMock(GetRecipeCommands::class);
        $getRecipeCommands->expects(self::once())
            ->method('forApplication')
            ->willReturn(['command']);

        $handler = new ExecuteJobHandler(
            $jobsCollection,
            $projectsCollection,
            $prepareApplicationGitRepository,
            $buildApplication,
            $this->createMock(Clock::class),
            $updateMergeRequest,
            $eventBusSpy,
            $this->createMock(ExecuteCommand::class),
            $this->createDetectApplicationLanguage(),
            $getRecipeCommands,
            $this->createMock(DetectContainerImage::class),
        );

        $handler->__invoke($command);
    }


    /**
     * @return Job&MockObject
     */
    private function createTaskJobMock(JobId $jobId): MockObject
    {
        return $this->createTestProxy(Job::class, [
                $jobId,
                new ProjectId(''),
                'Title',
                ['command'],
                $this->createMock(Clock::class),
            ]);
    }


    /**
     * @return Job&MockObject
     */
    private function createRecipeJobMock(JobId $jobId): MockObject
    {
        return $this->createTestProxy(Job::class, [
                $jobId,
                new ProjectId(''),
                'Title',
                null,
                $this->createMock(Clock::class),
                EnabledRecipe::withoutConfiguration(RecipeName::TYPED_PROPERTIES, 'abc'),
            ]);
    }


    /**
     * @return Project&MockObject
     */
    private function createProjectMock(): MockObject
    {
        return $this->createTestProxy(Project::class, [
                new ProjectId(''),
                new RemoteGitRepository('https://gitlab.com/peon/peon.git', GitRepositoryAuthentication::fromPersonalAccessToken('PAT')),
            ]);
    }


    /**
     * @return DetectApplicationLanguage&MockObject
     */
    private function createDetectApplicationLanguage(): MockObject
    {
        $mock = $this->createMock(DetectApplicationLanguage::class);
        $mock->method('inDirectory')
            ->willReturn(new ApplicationLanguage('PHP', '8.1'));

        return $mock;
    }
}
