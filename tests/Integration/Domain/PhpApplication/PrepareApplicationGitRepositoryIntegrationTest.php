<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Domain\PhpApplication;

use Peon\Domain\Application\Value\ApplicationGitRepositoryClone;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Application\PrepareApplicationGitRepository;
use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Tools\Git\Git;
use Peon\Infrastructure\Git\StatefulRandomPostfixProvideBranchName;
use Peon\Tests\TestingRemoteGitRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PrepareApplicationGitRepositoryIntegrationTest extends KernelTestCase
{
    private \Peon\Domain\Application\PrepareApplicationGitRepository $prepareApplicationGitRepository;
    private Git $git;
    private string $taskName = 'integration-test';
    private StatefulRandomPostfixProvideBranchName $branchNameProvider;


    protected function setUp(): void
    {
        $this->prepareApplicationGitRepository = self::getContainer()->get(PrepareApplicationGitRepository::class);
        $this->git = self::getContainer()->get(Git::class);
        $this->branchNameProvider = self::getContainer()->get(StatefulRandomPostfixProvideBranchName::class);
    }


    protected function tearDown(): void
    {
        $this->branchNameProvider->resetState();

        parent::tearDown();
    }


    public function testRemoteBranchNotExists(): void
    {
        $testingGitRepository = TestingRemoteGitRepository::init();
        $targetBranchName = $this->branchNameProvider->forTask($this->taskName);
        $jobId = new JobId(Uuid::uuid4()->toString());

        $temporaryApplication = $this->prepareApplicationGitRepository->forRemoteRepository(
            $jobId,
            $testingGitRepository->uri,
            $this->taskName
        );

        $this->assertTemporaryApplicationIsPrepared($temporaryApplication, $jobId, $targetBranchName);
    }


    public function testRemoteBranchExistsRebaseSucceeds(): void
    {
        $targetBranch = $this->branchNameProvider->forTask($this->taskName);
        $testingGitRepository = TestingRemoteGitRepository::init();
        $testingGitRepository->makeBranchBehindMain($targetBranch);

        $jobId = new JobId(Uuid::uuid4()->toString());

        $temporaryApplication = $this->prepareApplicationGitRepository->forRemoteRepository(
            $jobId,
            $testingGitRepository->uri,
            $this->taskName
        );

        $this->assertTemporaryApplicationIsPrepared($temporaryApplication, $jobId, $targetBranch);
    }


    public function testRemoteBranchExistsRebaseConflicts(): void
    {
        $targetBranch = $this->branchNameProvider->forTask($this->taskName);
        $testingGitRepository = TestingRemoteGitRepository::init();
        $testingGitRepository->makeBranchConflictAgainstMain($targetBranch);

        $jobId = new JobId(Uuid::uuid4()->toString());

        $temporaryApplication = $this->prepareApplicationGitRepository->forRemoteRepository(
            $jobId,
            $testingGitRepository->uri,
            $this->taskName
        );

        $this->assertTemporaryApplicationIsPrepared($temporaryApplication, $jobId, $targetBranch);
    }


    private function assertTemporaryApplicationIsPrepared(ApplicationGitRepositoryClone $temporaryApplication, JobId $jobId, string $branchName): void
    {
        self::assertSame(TestingRemoteGitRepository::MAIN_BRANCH, $temporaryApplication->mainBranch);
        self::assertSame($branchName, $this->git->getCurrentBranch($jobId, $temporaryApplication->workingDirectory->localPath));

        // TODO: we should check the remote repository as well!
    }
}
