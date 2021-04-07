<?php
declare(strict_types=1);

namespace PHPMate\Tests\UseCase;

use _HumbugBox5ccdb2ccdb35\Nette\DI\ContainerBuilder;
use PHPMate\Domain\Git\BranchNameProvider;
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

        $this->assertMergeRequestExists($branchName);
        $this->removeBranch($branchName);
    }


    private function assertMergeRequestExists(string $branchName): void
    {
        // TODO
    }


    private function removeBranch(string $branchName): void
    {
        // TODO
    }
}
