<?php
declare(strict_types=1);

namespace PHPMate\Tests\End2End;

use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Tools\Git\BranchNameProvider;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Domain\Tools\Rector\RectorCommandFailed;
use PHPMate\Infrastructure\GitLab\GitLab;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\UseCase\ExecuteJob;
use PHPMate\UseCase\ExecuteJobUseCase;
use PHPUnit\Framework\TestCase;

class ExecuteJobUseCaseTest extends TestCase
{
    private const JOB_ID = '0';
    private const PROJECT_ID = '0';

    private string $branchName;
    private RemoteGitRepository $gitlabRepository;
    private ExecuteJobUseCase $useCase;
    private Client $gitlabHttpClient;
    private JobsCollection $jobsCollection;
    private ProjectsCollection $projectsCollection;
    private Clock $clock;


    protected function setUp(): void
    {
        // Populate values in `.env.test.local`
        $repositoryUri = $_SERVER['TEST_GITLAB_REPOSITORY'];
        $username = $_SERVER['TEST_GITLAB_USERNAME'];
        $personalAccessToken = $_SERVER['TEST_GITLAB_PERSONAL_ACCESS_TOKEN'];

        $container = ContainerFactory::create();

        /** @var ExecuteJobUseCase $useCase */
        $useCase = $container->get(ExecuteJobUseCase::class);
        $this->useCase = $useCase;

        /** @var BranchNameProvider $branchNameProvider */
        $branchNameProvider = $container->get(BranchNameProvider::class);
        $this->branchName = $branchNameProvider->provideForTask('test');

        /** @var JobsCollection $jobsCollection */
        $jobsCollection = $container->get(JobsCollection::class);
        $this->jobsCollection = $jobsCollection;

        /** @var ProjectsCollection $projectsCollection */
        $projectsCollection = $container->get(ProjectsCollection::class);
        $this->projectsCollection = $projectsCollection;

        /** @var Clock $clock */
        $clock = $container->get(Clock::class);
        $this->clock = $clock;

        /** @var GitLab $gitLab */
        $gitLab = $container->get(GitLab::class);
        $authentication = new GitRepositoryAuthentication($username, $personalAccessToken);
        $this->gitlabRepository = new RemoteGitRepository($repositoryUri, $authentication);
        $this->gitlabHttpClient = $gitLab->createHttpClient($this->gitlabRepository);

        $this->prepareData();
    }


    protected function tearDown(): void
    {
        $this->deleteRemoteBranch($this->gitlabRepository->getProject(), $this->branchName);
    }


    /**
     * Scenario "Happy path":
     *  - remote branch does not exist, start over from main branch
     */
    public function testHappyPath(): void
    {
        $jobId = new JobId(self::JOB_ID);

        $this->useCase->handle(new ExecuteJob($jobId));

        $this->assertNonEmptyMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);

        self::assertTrue($this->jobsCollection->get($jobId)->hasSucceeded(), 'Job should be succeeded!');
    }


    /**
     * Scenario "Rebase & No changes":
     *  - remote branch already exists
     *  - checkout remote branch
     *  - successfully rebase
     *  - no changes - branch already contains changes in previous commits
     */
    public function testRemoteBranchAlreadyExistsRebaseSuccessesButNoChanges(): void
    {
        $this->duplicateBranch('already-processed', $this->branchName);

        $jobId = new JobId(self::JOB_ID);

        $this->useCase->handle(new ExecuteJob($jobId));

        $this->assertNonEmptyMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);

        self::assertTrue($this->jobsCollection->get($jobId)->hasSucceeded(), 'Job should be succeeded!');
    }


    /**
     * Scenario "Rebase & Conflict":
     *  - remote branch already exists
     *  - checkout remote branch
     *  - fails to rebase (conflicts)
     *  - resets branch HEAD to main branch
     *  - new changes committed
     */
    public function testRemoteBranchAlreadyExistsRebaseFails(): void
    {
        $this->duplicateBranch('conflict', $this->branchName);

        $jobId = new JobId(self::JOB_ID);

        $this->useCase->handle(new ExecuteJob($jobId));

        $this->assertNonEmptyMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);

        self::assertTrue($this->jobsCollection->get($jobId)->hasSucceeded(), 'Job should be succeeded!');
    }


    /**
     * Scenario "Rebase & No changes":
     *  - remote branch already exists
     *  - checkout remote branch
     *  - successfully rebase
     *  - new changes committed
     */
    public function testRemoteBranchAlreadyExistsRebaseSuccessesAndHaveChanges(): void
    {
        $this->duplicateBranch('to-be-rebased', $this->branchName);

        $jobId = new JobId(self::JOB_ID);

        $this->useCase->handle(new ExecuteJob($jobId));

        $this->assertNonEmptyMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);

        self::assertTrue($this->jobsCollection->get($jobId)->hasSucceeded(), 'Job should be succeeded!');
    }


    /**
     * Scenario "Process fails":
     *  - rector process fails
     *  - notification is dispatched
     *  - mr will not be opened
     */
    public function testProcessWillFailAndNotificationWillBeDispatched(): void
    {
        $this->duplicateBranch('process-fail', $this->branchName);

        $exception = null;
        $jobId = new JobId(self::JOB_ID);

        try {
            $this->useCase->handle(new ExecuteJob($jobId));
        } catch (\Throwable $exception) {
            // Just to capture
        }

        // TODO: Find way how to assert that notification was dispatched

        self::assertTrue($this->jobsCollection->get($jobId)->hasFailed(), 'Job should be failed!');
        self::assertInstanceOf(RectorCommandFailed::class, $exception);
        $this->assertMergeRequestNotExists($this->gitlabRepository->getProject(), $this->branchName);

    }


    private function assertNonEmptyMergeRequestExists(string $project, string $branchName): void
    {
        $mergeRequests = $this->findMergeRequests($project, $branchName);

        self::assertCount(1, $mergeRequests, 'Merge request should exist!');
        self::assertSame('master', $mergeRequests[0]['target_branch']);
        self::assertSame('[PHP Mate] Task End2End Test', $mergeRequests[0]['title']);

        $commits = $this->gitlabHttpClient->mergeRequests()->commits($project,$mergeRequests[0]['iid']);

        self::assertNotEmpty($commits, 'Merge request should contain commits!');
    }


    private function assertMergeRequestNotExists(string $project, string $branchName): void
    {
        $mergeRequests = $this->findMergeRequests($project, $branchName);

        self::assertCount(0, $mergeRequests);
    }


    private function deleteRemoteBranch(string $project, string $branchName): void
    {
        try {
            $this->gitlabHttpClient->repositories()->deleteBranch($project, $branchName);
        } catch (RuntimeException $runtimeException) {
            // To not escalate 404 errors
            if ($runtimeException->getCode() !== 404) {
                throw $runtimeException;
            }
        }
    }


    private function duplicateBranch(string $sourceBranch, string $targetBranch): void
    {
        $this->gitlabHttpClient->repositories()->createBranch(
            $this->gitlabRepository->getProject(),
            $targetBranch,
            $sourceBranch
        );
    }


    /**
     * @return array<mixed>
     */
    private function findMergeRequests(string $project, string $sourceBranch): array
    {
        return $this->gitlabHttpClient->mergeRequests()->all($project, [
            'state' => 'opened',
            'source_branch' => $sourceBranch,
        ]);
    }


    private function prepareData(): void
    {
        $projectId = new ProjectId(self::PROJECT_ID);
        $project = new Project(
            $projectId,
            'Test',
           $this->gitlabRepository
        );

        $this->projectsCollection->save($project);

        $job = new Job(
            new JobId(self::JOB_ID),
            $projectId,
            'End2End Test',
            $this->clock->now()->getTimestamp(),
            ['vendor/bin/rector process']
        );

        $this->jobsCollection->save($job);
    }
}
