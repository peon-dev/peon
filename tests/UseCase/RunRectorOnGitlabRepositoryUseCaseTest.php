<?php
declare(strict_types=1);

namespace PHPMate\Tests\UseCase;

use Gitlab\Client;
use PHPMate\Domain\Git\BranchNameProvider;
use PHPMate\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Infrastructure\Gitlab\HttpGitlab;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\UseCase\RunRectorOnGitlabRepository;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;
use PHPUnit\Framework\TestCase;

// TODO: test more scenarios with existing branch already
class RunRectorOnGitlabRepositoryUseCaseTest extends TestCase
{
    private string $branchName;
    private GitlabRepository $gitlabRepository;
    private RunRectorOnGitlabRepositoryUseCase $useCase;
    private Client $gitlabHttpClient;


    protected function setUp(): void
    {
        // Populate values in `.env.test.local`
        $repositoryUri = $_SERVER['TEST_GITLAB_REPOSITORY'];
        $username = $_SERVER['TEST_GITLAB_USERNAME'];
        $personalAccessToken = $_SERVER['TEST_GITLAB_PERSONAL_ACCESS_TOKEN'];

        $container = ContainerFactory::create();

        /** @var RunRectorOnGitlabRepositoryUseCase $useCase */
        $useCase = $container->get(RunRectorOnGitlabRepositoryUseCase::class);
        $this->useCase = $useCase;

        /** @var BranchNameProvider $branchNameProvider */
        $branchNameProvider = $container->get(BranchNameProvider::class);
        $this->branchName = $branchNameProvider->provideForProcedure('rector');

        /** @var HttpGitlab $httpGitlab */
        $httpGitlab = $container->get(HttpGitlab::class);
        $authentication = new GitlabAuthentication($username, $personalAccessToken);
        $this->gitlabRepository = new GitlabRepository($repositoryUri, $authentication);
        $this->gitlabHttpClient = $httpGitlab->createHttpClient($this->gitlabRepository);
    }


    protected function tearDown(): void
    {
        $this->deleteRemoteBranch($this->gitlabRepository->getProject(), $this->branchName);
    }


    /**
     *  @todo describe scenario
     */
    public function testHappyPath(): void
    {
        $this->useCase->__invoke(new RunRectorOnGitlabRepository($this->gitlabRepository));

        $this->assertMergeRequestExists($this->gitlabRepository->getProject(), $this->branchName);
    }


    /**
     *  @todo describe scenario
     */
    public function testRemoteBranchAlreadyExistsRebaseSuccesses(): void
    {
    }


    /**
     *  @todo describe scenario
     */
    public function testRemoteBranchAlreadyExistsWithConflicts(): void
    {
    }


    private function assertMergeRequestExists(string $project, string $branchName): void
    {
        $mergeRequests = $this->gitlabHttpClient->mergeRequests()->all($project, [
            'state' => 'opened',
            'source_branch' => $branchName,
        ]);

        self::assertCount(1, $mergeRequests);
        self::assertSame('master', $mergeRequests[0]['target_branch']);
        self::assertSame('Rector run by PHPMate', $mergeRequests[0]['title']);
    }


    private function deleteRemoteBranch(string $project, string $branchName): void
    {
        $this->gitlabHttpClient->repositories()->deleteBranch($project, $branchName);
    }
}
