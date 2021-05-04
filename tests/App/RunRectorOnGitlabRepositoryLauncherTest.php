<?php
declare(strict_types=1);

namespace PHPMate\Tests\App;

use PHPMate\App\RunRectorOnGitlabRepositoryLauncher;
use PHPMate\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Domain\Job\JobRepository;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ContainerFactory;
use PHPMate\UseCase\RunRectorOnGitlabRepository;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;
use PHPUnit\Framework\TestCase;

class RunRectorOnGitlabRepositoryLauncherTest extends TestCase
{
    private GitlabRepository $gitlabRepository;
    private JobRepository $jobRepository;


    protected function setUp(): void
    {
        $container = ContainerFactory::create();

        /** @var JobRepository $jobRepository */
        $jobRepository = $container->get(JobRepository::class);
        $this->jobRepository = $jobRepository;

        $authentication = new GitlabAuthentication('', '');
        $this->gitlabRepository = new GitlabRepository('https://gitlab.com/phpmate-dogfood/rector.git', $authentication);
    }


    /**
     * Scenario "Happy path":
     *  - 0 jobs in collection
     *  - run use case
     *  - 1 job in collection
     *
     * @dataProvider provideTestLaunchData
     */
    public function testLaunch(bool $shouldThrowException): void
    {
        self::assertCount(0, $this->jobRepository->findAll());

        $useCase = $this->createMock(RunRectorOnGitlabRepositoryUseCase::class);

        if ($shouldThrowException === true) {
            $useCase->method('__invoke')
                ->willThrowException(new \Exception());
        }

        try {
            $launcher = new RunRectorOnGitlabRepositoryLauncher($useCase);
            $launcher->launch(new RunRectorOnGitlabRepository($this->gitlabRepository));
        } catch (\Throwable) {}

        self::assertCount(1, $this->jobRepository->findAll());
    }


    /**
     * @return \Generator<array{bool}>
     */
    public function provideTestLaunchData(): \Generator
    {
        yield [false];
        yield [true];
    }
}
