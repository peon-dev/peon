<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use Lcobucci\Clock\SystemClock;
use PHPMate\App\RunRectorOnGitlabRepositoryLauncher;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Job\JobStatus;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Infrastructure\Memory\InMemoryJobsCollection;
use PHPMate\UseCase\RunRectorOnGitlabRepository;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;
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
        $jobRepository = new InMemoryJobsCollection();
        $authentication = new GitRepositoryAuthentication('', '');
        $gitlabRepository = new RemoteGitRepository('https://gitlab.com/phpmate-dogfood/rector.git', $authentication);
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
        self::assertSame($expectedStatus, $jobs[array_key_first($jobs)]->status);
    }


    /**
     * @return \Generator<array{bool, string}>
     */
    public function provideTestLaunchData(): \Generator
    {
        yield [false, JobStatus::SUCCEEDED];

        yield [true, JobStatus::FAILED];
    }
}
