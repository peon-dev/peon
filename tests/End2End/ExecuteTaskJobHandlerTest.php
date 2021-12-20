<?php
declare(strict_types=1);

namespace PHPMate\Tests\End2End;

use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\Exception\JobExecutionFailed;
use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Process\Exception\ProcessFailed;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Tools\Git\BranchNameProvider;
use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Infrastructure\Git\StatefulRandomPostfixBranchNameProvider;
use PHPMate\Infrastructure\GitLab\GitLab;
use PHPMate\UseCase\ExecuteTaskJob;
use PHPMate\UseCase\ExecuteTaskJobHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExecuteTaskJobHandlerTest extends KernelTestCase
{
    private const JOB_ID = 'a59be334-15e4-4b53-bc59-9900200af917';
    private const TASK_ID = '67081fd2-3922-46ef-82b1-df5d02ad6f3e';
    private const PROJECT_ID = 'f969c281-65f4-46ff-b77d-aff33a7da07c';

    private string $branchName;
    private RemoteGitRepository $gitlabRepository;
    private ExecuteTaskJobHandler $useCase;
    private Client $gitlabHttpClient;
    private JobsCollection $jobsCollection;
    private ProjectsCollection $projectsCollection;
    private Clock $clock;
    private StatefulRandomPostfixBranchNameProvider $branchNameProvider;


    protected function setUp(): void
    {
        // Populate values in `.env.test.local`
        $repositoryUri = $_SERVER['TEST_GITLAB_REPOSITORY'];
        $username = $_SERVER['TEST_GITLAB_USERNAME'];
        $personalAccessToken = $_SERVER['TEST_GITLAB_PERSONAL_ACCESS_TOKEN'];

        $container = self::getContainer();

        $this->useCase = $container->get(ExecuteTaskJobHandler::class);
        $this->branchNameProvider = $container->get(StatefulRandomPostfixBranchNameProvider::class);
        $this->branchName = $this->branchNameProvider->provideForTask('test');
        $this->jobsCollection = $container->get(JobsCollection::class);
        $this->projectsCollection = $container->get(ProjectsCollection::class);
        $this->clock = $container->get(Clock::class);

        $gitLab = $container->get(GitLab::class);
        $authentication = new GitRepositoryAuthentication($username, $personalAccessToken);
        $this->gitlabRepository = new RemoteGitRepository($repositoryUri, $authentication);
        $this->gitlabHttpClient = $gitLab->createHttpClient($this->gitlabRepository);

        $this->prepareData();
    }


    protected function tearDown(): void
    {
        $this->deleteRemoteBranch($this->gitlabRepository->getProject(), $this->branchName);
        $this->branchNameProvider->resetState();

        parent::tearDown();
    }


    /**
     * Scenario "Happy path":
     *  - remote branch does not exist, start over from main branch
     */
    public function testHappyPath(): void
    {
        $jobId = new JobId(self::JOB_ID);

        $this->useCase->__invoke(new ExecuteTaskJob($jobId));

        $this->assertNonEmptyMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);

        $job = $this->jobsCollection->get($jobId);
        $this->assertJobHasSucceed($job);
        self::assertNotEmpty($job->processes, 'Job should contain processes!');
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

        $this->assertMergeRequestNotExists($this->gitlabRepository->getProject(), $this->branchName);

        $jobId = new JobId(self::JOB_ID);
        $this->useCase->__invoke(new ExecuteTaskJob($jobId));

        $this->assertNonEmptyMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);

        $job = $this->jobsCollection->get($jobId);
        $this->assertJobHasSucceed($job);
        self::assertNotEmpty($job->processes, 'Job should contain processes!');
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
        $this->duplicateBranch('conflict', $this->branchName);

        $jobId = new JobId(self::JOB_ID);

        $this->useCase->__invoke(new ExecuteTaskJob($jobId));

        $this->assertNonEmptyMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);

        $job = $this->jobsCollection->get($jobId);
        $this->assertJobHasSucceed($job);
        self::assertNotEmpty($job->processes, 'Job should contain processes!');
        self::assertNotNull($job->mergeRequest);
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

        $this->useCase->__invoke(new ExecuteTaskJob($jobId));

        $this->assertNonEmptyMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);

        $job = $this->jobsCollection->get($jobId);
        $this->assertJobHasSucceed($job);
        self::assertNotEmpty($job->processes, 'Job should contain processes!');
        self::assertNotNull($job->mergeRequest);
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
            $this->useCase->__invoke(new ExecuteTaskJob($jobId));
        } catch (\Throwable $exception) {
            // Just to capture
        }

        // TODO: Find way how to assert that notification was dispatched

        $job = $this->jobsCollection->get($jobId);
        $this->assertJobHasFailed($job);
        self::assertNotEmpty($job->processes, 'Job should contain processes!');
        // TODO there should be exact count of processes
        self::assertInstanceOf(JobExecutionFailed::class, $exception);
        $this->assertMergeRequestNotExists($this->gitlabRepository->getProject(), $this->branchName);
        self::assertNull($job->mergeRequest);

    }


    private function assertNonEmptyMergeRequestExists(string $project, string $branchName): void
    {
        $mergeRequests = $this->findMergeRequests($project, $branchName);

        self::assertCount(1, $mergeRequests, 'Merge request should be opened!');
        self::assertSame('master', $mergeRequests[0]['target_branch']);
        self::assertSame('[PHP Mate] End2End Test', $mergeRequests[0]['title']);

        $commits = $this->gitlabHttpClient->mergeRequests()->commits($project,$mergeRequests[0]['iid']);

        self::assertNotEmpty($commits, 'Merge request should contain commits!');
    }


    private function assertMergeRequestNotExists(string $project, string $branchName): void
    {
        $mergeRequests = $this->findMergeRequests($project, $branchName);

        self::assertCount(0, $mergeRequests, 'Merge request should not be opened!');
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
     * @return array<array{target_branch: string, title: string, iid: int}>
     */
    private function findMergeRequests(string $project, string $sourceBranch): array
    {
        /** @var array<array{target_branch: string, title: string, iid: int}> $mergeRequests */
        $mergeRequests = $this->gitlabHttpClient->mergeRequests()->all($project, [
            'state' => 'opened',
            'source_branch' => $sourceBranch,
        ]);

        return $mergeRequests;
    }


    // TODO: instead of directly persisting, we could use fixtures or handlers
    private function prepareData(): void
    {
        $projectId = new ProjectId(self::PROJECT_ID);
        $taskId = new TaskId(self::TASK_ID);
        $project = new Project(
            $projectId,
            $this->gitlabRepository
        );

        $this->projectsCollection->save($project);

        $job = new Job(
            new JobId(self::JOB_ID),
            $projectId,
            'End2End Test',
            ['vendor/bin/rector process'],
            $this->clock,
        );

        $this->jobsCollection->save($job);
    }


    private function assertJobHasSucceed(Job $job): void
    {
        self::assertNotNull($job->succeededAt, 'Job should be succeeded!');
    }


    private function assertJobHasFailed(Job $job): void
    {
        self::assertNotNull($job->failedAt, 'Job should be succeeded!');
    }
}
