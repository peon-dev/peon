<?php
declare(strict_types=1);

namespace PHPMate\Tests\End2End;

use Gitlab\Client;
use PHPMate\Domain\Job\JobId;
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

    private string $branchName;
    private RemoteGitRepository $gitlabRepository;
    private ExecuteJobUseCase $useCase;
    private Client $gitlabHttpClient;


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

        /** @var GitLab $gitLab */
        $gitLab = $container->get(GitLab::class);
        $authentication = new GitRepositoryAuthentication($username, $personalAccessToken);
        $this->gitlabRepository = new RemoteGitRepository($repositoryUri, $authentication);
        $this->gitlabHttpClient = $gitLab->createHttpClient($this->gitlabRepository);
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
        $this->useCase->handle(new ExecuteJob(new JobId(self::JOB_ID)));

        $this->assertMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);
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

        $this->useCase->handle(new ExecuteJob(new JobId(self::JOB_ID)));

        $this->assertMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);
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

        $this->useCase->handle(new ExecuteJob(new JobId(self::JOB_ID)));

        $this->assertMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);
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

        $this->useCase->handle(new ExecuteJob(new JobId(self::JOB_ID)));

        $this->assertMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);
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

        try {
            $this->useCase->handle(new ExecuteJob(new JobId(self::JOB_ID)));
        } catch (\Throwable $exception) {
            // Just to capture
        }

        // TODO: Find way how to assert that notification was dispatched

        self::assertInstanceOf(RectorCommandFailed::class, $exception);
        $this->assertMergeRequestNotExists($this->gitlabRepository->getProject(), $this->branchName);
    }


    private function assertMergeRequestExists(string $project, string $branchName): void
    {
        $mergeRequests = $this->findMergeRequests($project, $branchName);

        self::assertCount(1, $mergeRequests);
        self::assertSame('master', $mergeRequests[0]['target_branch']);
        self::assertSame('Rector run by PHPMate', $mergeRequests[0]['title']);
    }


    private function assertMergeRequestNotExists(string $project, string $branchName): void
    {
        $mergeRequests = $this->findMergeRequests($project, $branchName);

        self::assertCount(0, $mergeRequests);
    }


    private function deleteRemoteBranch(string $project, string $branchName): void
    {
        $this->gitlabHttpClient->repositories()->deleteBranch($project, $branchName);
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
}
