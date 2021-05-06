<?php
declare(strict_types=1);

namespace PHPMate\Worker\Tests\App;

use Lcobucci\Clock\SystemClock;
use PHPMate\Worker\App\RunRectorOnGitlabRepositoryLauncher;
use PHPMate\Worker\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Worker\Domain\Gitlab\GitlabRepository;
use PHPMate\Worker\Domain\Job\Job;
use PHPMate\Worker\Domain\Job\JobRepository;
use PHPMate\Worker\Domain\Process\ProcessLogger;
use PHPMate\Worker\Infrastructure\Memory\InMemoryJobRepository;
use PHPMate\Worker\UseCase\RunRectorOnGitlabRepository;
use PHPMate\Worker\UseCase\RunRectorOnGitlabRepositoryUseCase;
use PHPUnit\Framework\TestCase;

class RunRectorOnGitlabRepositoryLauncherTest extends TestCase
{
    /**
     * Scenario "Happy path":
     *  - 0 jobs in collection
     *  - run use case
     *  - 1 job in collection with expected status
     *
     * @dataProvider provideTestLaunchData
     */
    public function testLaunch(bool $shouldThrowException, string $expectedStatus): void
    {
        $jobRepository = new InMemoryJobRepository();
        $authentication = new GitlabAuthentication('', '');
        $gitlabRepository = new GitlabRepository('https://gitlab.com/phpmate-dogfood/rector.git', $authentication);
        $useCase = $this->createMock(RunRectorOnGitlabRepositoryUseCase::class);

        if ($shouldThrowException === true) {
            $useCase->method('__invoke')
                ->willThrowException(new \Exception());
        }

        self::assertCount(0, $jobRepository->findAll());

        try {
            $launcher = new RunRectorOnGitlabRepositoryLauncher(
                $useCase,
                $jobRepository,
                new ProcessLogger(),
                new SystemClock(new \DateTimeZone('UTC')),
            );
            $launcher->launch(new RunRectorOnGitlabRepository($gitlabRepository));
        } catch (\Throwable) {}

        $jobs = $jobRepository->findAll();

        self::assertCount(1, $jobs);
        self::assertSame($expectedStatus, $jobs[array_key_first($jobs)]->getStatus());
    }


    /**
     * @return \Generator<array{bool, string}>
     */
    public function provideTestLaunchData(): \Generator
    {
        yield [false, Job::STATUS_SUCCEEDED];

        yield [true, Job::STATUS_FAILED];
    }
}
