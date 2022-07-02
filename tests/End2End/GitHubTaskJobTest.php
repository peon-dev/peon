<?php

declare(strict_types=1);

namespace Peon\Tests\End2End;

use Github\Client;
use Lcobucci\Clock\Clock;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\Git\StatefulRandomPostfixProvideBranchName;
use Peon\Infrastructure\GitProvider\GitHub;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\Process\ProvideReadProcessesByJobId;
use Peon\UseCase\ExecuteJob;
use Peon\UseCase\ExecuteJobHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GitHubTaskJobTest extends KernelTestCase
{
    // Just some random ids for test purposes
    private const JOB_ID = '00000000-0000-0000-0000-000000000000';
    private const TASK_ID = '00000000-0000-0000-0000-000000000000';
    private const PROJECT_ID = '00000000-0000-0000-0000-000000000000';

    private string $branchName;
    private RemoteGitRepository $remoteGitRepository;
    private ExecuteJobHandler $useCase;
    private Client $gitHubClient;
    private JobsCollection $jobsCollection;
    private ProjectsCollection $projectsCollection;
    private Clock $clock;
    private StatefulRandomPostfixProvideBranchName $branchNameProvider;
    private ProvideReadProcessesByJobId $provideReadProcessesByJobId;


    protected function setUp(): void
    {
        // Populate values in `.env.test.local`
        $repositoryUri = $_SERVER['TEST_GITHUB_REPOSITORY'];
        $username = $_SERVER['TEST_GITHUB_USERNAME'];
        $personalAccessToken = $_SERVER['TEST_GITHUB_PERSONAL_ACCESS_TOKEN'];

        $container = self::getContainer();

        // Force to use GitHub provider over default DummyProvider for tests
        $gitHub = $container->get(GitHub::class);
        if ($container->initialized(GitProvider::class) === false) {
            $container->set(GitProvider::class, $gitHub);
        }

        $this->useCase = $container->get(ExecuteJobHandler::class);
        $this->branchNameProvider = $container->get(StatefulRandomPostfixProvideBranchName::class);
        $this->branchName = $this->branchNameProvider->forTask('test');
        $this->jobsCollection = $container->get(JobsCollection::class);
        $this->projectsCollection = $container->get(ProjectsCollection::class);
        $this->clock = $container->get(Clock::class);
        $this->provideReadProcessesByJobId = $container->get(ProvideReadProcessesByJobId::class);

        $authentication = new GitRepositoryAuthentication($username, $personalAccessToken);
        $this->remoteGitRepository = new RemoteGitRepository($repositoryUri, $authentication);
        $gitHub = $container->get(GitHub::class);
        $this->gitHubClient = $gitHub->createClient($this->remoteGitRepository);

        $this->prepareData();
    }


    protected function tearDown(): void
    {
        $this->deleteRemoteBranch($this->branchName);
        $this->branchNameProvider->resetState();

        self::getContainer()->reset();

        parent::tearDown();
    }


    /**
     * Scenario "Happy path":
     *  - remote branch does not exist, start over from main branch
     */
    public function testHappyPath(): void
    {
        $jobId = new JobId(self::JOB_ID);

        $this->useCase->__invoke(new ExecuteJob($jobId, false));

        $this->assertNonEmptyMergeRequestExists($this->branchName);

        $job = $this->jobsCollection->get($jobId);
        $this->assertJobHasSucceed($job);
        $this->assertJobProcessesExists($jobId);
        self::assertNotNull($job->mergeRequest);
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

        $this->assertMergeRequestNotExists($this->branchName);

        $jobId = new JobId(self::JOB_ID);
        $this->useCase->__invoke(new ExecuteJob($jobId, false));

        $this->assertNonEmptyMergeRequestExists($this->branchName);

        $job = $this->jobsCollection->get($jobId);
        $this->assertJobHasSucceed($job);
        $this->assertJobProcessesExists($jobId);
        self::assertNotNull($job->mergeRequest);
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
        $this->markTestIncomplete();
    }


    public function testMergeRequestAlreadyExists(): void
    {
        $this->markTestIncomplete();
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
        $this->markTestIncomplete();
    }


    /**
     * Scenario "Process fails":
     *  - job process fails
     *  - notification is dispatched
     *  - mr will not be opened
     */
    public function testProcessWillFailAndNotificationWillBeDispatched(): void
    {
        $this->duplicateBranch('process-fail', $this->branchName);

        $exception = null;
        $jobId = new JobId(self::JOB_ID);

        try {
            $this->useCase->__invoke(new ExecuteJob($jobId, false));
        } catch (\Throwable $exception) {
            // Just to capture
        }

        $job = $this->jobsCollection->get($jobId);
        $this->assertJobHasFailed($job);
        $this->assertJobProcessesExists($jobId);
        self::assertInstanceOf(JobExecutionFailed::class, $exception);
        $this->assertMergeRequestNotExists($this->branchName);
        self::assertNull($job->mergeRequest);
    }


    private function assertNonEmptyMergeRequestExists(string $branchName): void
    {
        $repositoryOwner = $this->remoteGitRepository->getProjectUsername();
        $mergeRequests = $this->findMergeRequests($branchName);

        self::assertCount(1, $mergeRequests, 'Merge request should be opened!');
        self::assertSame('main', $mergeRequests[0]['base']['ref']);
        self::assertSame('[Peon] End2End Test', $mergeRequests[0]['title']);

        $commits = $this->gitHubClient->pullRequests()->commits(
            $repositoryOwner,
            $this->remoteGitRepository->getProjectRepository(),
            $mergeRequests[0]['number'],
        );

        self::assertNotEmpty($commits, 'Merge request should contain commits!');
    }


    private function assertMergeRequestNotExists(string $branchName): void
    {
        $mergeRequests = $this->findMergeRequests($branchName);

        self::assertCount(0, $mergeRequests, 'Merge request should not be opened!');
    }


    private function deleteRemoteBranch(string $branchName): void
    {
        $this->gitHubClient->git()->references()->remove(
            $this->remoteGitRepository->getProjectUsername(),
            $this->remoteGitRepository->getProjectRepository(),
            'heads/' . $branchName,
        );
    }


    private function duplicateBranch(string $sourceBranch, string $targetBranch): void
    {
        $sourceBranchResponse = $this->gitHubClient->git()->references()->show(
            $this->remoteGitRepository->getProjectUsername(),
            $this->remoteGitRepository->getProjectRepository(),
            'heads/' . $sourceBranch,
        );

        $this->gitHubClient->git()->references()->create(
            $this->remoteGitRepository->getProjectUsername(),
            $this->remoteGitRepository->getProjectRepository(),
            [
                'ref' => 'refs/heads/' . $targetBranch,
                'sha' => $sourceBranchResponse['object']['sha'],
            ],
        );
    }


    private function findMergeRequests(string $sourceBranch): array
    {
        $repositoryOwner = $this->remoteGitRepository->getProjectUsername();

        $pullRequests = $this->gitHubClient->pullRequests()->all(
            $repositoryOwner,
            $this->remoteGitRepository->getProjectRepository(),
            [
                'state' => 'open',
                'head' => $repositoryOwner . ':' . $sourceBranch,
            ]
        );

        return $pullRequests;
    }


    // TODO: instead of directly persisting, we could use fixtures or handlers
    private function prepareData(): void
    {
        $projectId = new ProjectId(self::PROJECT_ID);
        $taskId = new TaskId(self::TASK_ID);
        $ownerUserId = new UserId(DataFixtures::USER_1_ID);
        $project = new Project(
            $projectId,
            $this->remoteGitRepository,
            $ownerUserId,
        );

        $this->projectsCollection->save($project);

        $job = new Job(
            new JobId(self::JOB_ID),
            $projectId,
            'End2End Test',
            ['vendor/bin/rector process'],
            $this->clock,
            taskId: $taskId,
        );

        $this->jobsCollection->save($job);
    }


    private function assertJobHasSucceed(Job $job): void
    {
        self::assertNotNull($job->succeededAt, 'Job should be succeeded!');
    }


    private function assertJobHasFailed(Job $job): void
    {
        self::assertNotNull($job->failedAt, 'Job should be failed!');
    }


    private function assertJobProcessesExists(JobId $jobId): void
    {
        $processes = $this->provideReadProcessesByJobId->provide($jobId);

        self::assertNotEmpty($processes, 'No processes for job found!');
    }
}
