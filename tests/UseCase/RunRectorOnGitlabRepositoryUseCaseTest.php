<?php
declare(strict_types=1);

namespace PHPMate\Tests\UseCase;

use PHPMate\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;
use PHPUnit\Framework\TestCase;

class RunRectorOnGitlabRepositoryUseCaseTest extends TestCase
{
    // TODO: test needs dynamic branch, because of parallel processing
    // TODO: we need to verify MR is created, how?
    // TODO: test should clean after itself, how? (delete branch + MR?)
    public function test(): void
    {
        $container = ContainerFactory::create();
        $useCase = $container->get(RunRectorOnGitlabRepositoryUseCase::class);

        self::assertInstanceOf(RunRectorOnGitlabRepositoryUseCase::class, $useCase);

        // Populate values in `.env.test.local`
        // $repositoryUri = $_ENV['TEST_GITLAB_REPOSITORY'];
        // $username = $_ENV['TEST_GITLAB_USERNAME'];
        // $personalAccessToken = $_ENV['TEST_GITLAB_PERSONAL_ACCESS_TOKEN'];

        // var_dump($_ENV['TEST_GITLAB_USERNAME']);
        var_dump($_SERVER['TEST_GITLAB_USERNAME']);

        die();

        $useCase->__invoke($repositoryUri, $username, $personalAccessToken);
    }
}
