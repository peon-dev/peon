<?php
declare(strict_types=1);

namespace PHPMate\Tests\UseCase;

use Gitlab\Client;
use PHPMate\Domain\Git\BranchNameProvider;
use PHPMate\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Infrastructure\Gitlab\HttpGitlabClient;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;
use PHPUnit\Framework\TestCase;

class RunRectorOnGitlabRepositoryUseCaseTest extends TestCase
{
    public function test(): void
    {
        $container = ContainerFactory::create();

        $useCase = $container->get(RunRectorOnGitlabRepositoryUseCase::class);
        self::assertInstanceOf(RunRectorOnGitlabRepositoryUseCase::class, $useCase);

        // Populate values in `.env.test.local`
        $repositoryUri = $_SERVER['TEST_GITLAB_REPOSITORY'];
        $username = $_SERVER['TEST_GITLAB_USERNAME'];
        $personalAccessToken = $_SERVER['TEST_GITLAB_PERSONAL_ACCESS_TOKEN'];

        $useCase->__invoke($repositoryUri, $username, $personalAccessToken);

        $branchNameProvider = $container->get(BranchNameProvider::class);
        self::assertInstanceOf(BranchNameProvider::class, $branchNameProvider);
        $branchName = $branchNameProvider->provideForProcedure('rector');

        $gitlabRepository = $this->getGitlabRepository($repositoryUri, $username, $personalAccessToken);
        $httpGitlab = $container->get(HttpGitlabClient::class);
        self::assertInstanceOf(HttpGitlabClient::class, $httpGitlab);
        $client = $httpGitlab->createClient($gitlabRepository);

        $this->assertMergeRequestExists($client, $branchName);
        $this->removeBranch($branchName);
    }

    // TODO: teardown


    private function assertMergeRequestExists(Client $client, string $branchName): void
    {
        $mergeRequests = $client->mergeRequests()->all(parameters: [
            'state' => 'opened',
            'source_branch' => $branchName,
        ]);

        self::assertCount(1, $mergeRequests);
        self::assertSame('master', $mergeRequests[0]['target_branch']);
        self::assertSame('Rector run by PHPMate', $mergeRequests[0]['title']);
    }


    private function removeBranch(string $branchName): void
    {
        // TODO
    }


    private function getGitlabRepository(string $repositoryUri, string $username, string $personalAccessToken): GitlabRepository
    {
        $authentication = new GitlabAuthentication($username, $personalAccessToken);

        return new GitlabRepository($repositoryUri, $authentication);
    }
}
