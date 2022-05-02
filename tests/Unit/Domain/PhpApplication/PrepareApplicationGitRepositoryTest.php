<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\PhpApplication;

use Peon\Domain\Application\Value\WorkingDirectory;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Application\ProvideApplicationDirectory;
use Peon\Domain\Application\PrepareApplicationGitRepository;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Tools\Git\ProvideBranchName;
use Peon\Domain\Tools\Git\Git;
use PHPUnit\Framework\TestCase;

class PrepareApplicationGitRepositoryTest extends TestCase
{
    private JobId $jobId;


    protected function setUp(): void
    {
        $this->jobId = new JobId('');
    }


    public function testLocalApplicationWillBePrepared(): void
    {
        $git = $this->createMock(Git::class);
        $git->expects(self::once())
            ->method('clone');
        $git->expects(self::once())
            ->method('configureUser');
        $git->expects(self::once())
            ->method('getCurrentBranch')
            ->willReturn('main');
        $git->expects(self::once())
            ->method('switchToBranch')
            ->with($this->jobId, '/', 'task');
        $git->expects(self::once())
            ->method('remoteBranchExists')
            ->with($this->jobId, '/', 'task')
            ->willReturn(false);

        $projectDirectoryProvider = $this->createMock(ProvideApplicationDirectory::class);
        $workingDirectory = new WorkingDirectory('/local', '/host');
        $projectDirectoryProvider->expects(self::once())
            ->method('provide')
            ->willReturn($workingDirectory);

        $branchNameProvider = $this->createMock(ProvideBranchName::class);
        $branchNameProvider->expects(self::once())
            ->method('forTask')
            ->with('Task')
            ->willReturn('task');

        $prepareApplicationGitRepository = new PrepareApplicationGitRepository(
            $git,
            $projectDirectoryProvider,
            $branchNameProvider,
        );

        $localApplication = $prepareApplicationGitRepository->forRemoteRepository(
            $this->jobId,
            $this->getRemoteGitRepository()->getAuthenticatedUri(),
            'Task',
        );

        self::assertSame('main', $localApplication->mainBranch);
        self::assertSame($workingDirectory, $localApplication->workingDirectory);
        self::assertSame('task', $localApplication->jobBranch);
    }


    public function testRemoteBranchExistsRebaseSucceededWillBeForcePushed(): void
    {
        $git = $this->createMock(Git::class);
        $git->method('remoteBranchExists')
            ->willReturn(true);
        $git->method('getCurrentBranch')
            ->willReturn('main');

        $git->expects(self::once())
            ->method('pull')
            ->with($this->jobId, '/');
        $git->expects(self::once())
            ->method('rebaseBranchAgainstUpstream')
            ->with($this->jobId, '/', 'main');
        $git->expects(self::once())
            ->method('forcePushWithLease');

        $projectDirectoryProvider = $this->createMock(ProvideApplicationDirectory::class);
        $projectDirectoryProvider->expects(self::once())
            ->method('provide')
            ->willReturn('/');

        $branchNameProvider = $this->createMock(ProvideBranchName::class);

        $prepareApplicationGitRepository = new PrepareApplicationGitRepository(
            $git,
            $projectDirectoryProvider,
            $branchNameProvider,
        );

        $prepareApplicationGitRepository->forRemoteRepository(
            $this->jobId,
            $this->getRemoteGitRepository()->getAuthenticatedUri(),
            'Task',
        );
    }


    public function testRemoteBranchExistsRebaseFailedBranchWillBeReset(): void
    {
        $git = $this->createMock(Git::class);
        $git->method('remoteBranchExists')
            ->willReturn(true);
        $git->method('getCurrentBranch')
            ->willReturn('main');
        $git->method('rebaseBranchAgainstUpstream')
            ->willThrowException(new ProcessFailed(new ProcessResult(1, 0, '')));

        $git->expects(self::once())
            ->method('abortRebase');
        $git->expects(self::once())
            ->method('resetCurrentBranch')
            ->with($this->jobId, '/', 'main');

        $projectDirectoryProvider = $this->createMock(ProvideApplicationDirectory::class);
        $projectDirectoryProvider->expects(self::once())
            ->method('provide')
            ->willReturn('/');

        $branchNameProvider = $this->createMock(ProvideBranchName::class);

        $prepareApplicationGitRepository = new PrepareApplicationGitRepository(
            $git,
            $projectDirectoryProvider,
            $branchNameProvider,
        );

        $prepareApplicationGitRepository->forRemoteRepository(
            $this->jobId,
            $this->getRemoteGitRepository()->getAuthenticatedUri(),
            'Task',
        );
    }


    public function testRemoteBranchExistsAndWillBeCheckedOut(): void
    {
        $git = $this->createMock(Git::class);
        $git->method('remoteBranchExists')
            ->willReturn(true);
        $git->method('getCurrentBranch')
            ->willReturn('main');

        $git->expects(self::once())
            ->method('trackRemoteBranch')
            ->with($this->jobId, '/', 'task');

        $projectDirectoryProvider = $this->createMock(ProvideApplicationDirectory::class);
        $projectDirectoryProvider->expects(self::once())
            ->method('provide')
            ->willReturn('/');

        $branchNameProvider = $this->createBranchNameProvider();

        $prepareApplicationGitRepository = new PrepareApplicationGitRepository(
            $git,
            $projectDirectoryProvider,
            $branchNameProvider,
        );

        $prepareApplicationGitRepository->forRemoteRepository(
            $this->jobId,
            $this->getRemoteGitRepository()->getAuthenticatedUri(),
            'Task',
        );
    }


    private function getRemoteGitRepository(): RemoteGitRepository
    {
        return new RemoteGitRepository(
            'https://gitlab.com/peon/peon.git',
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );
    }


    private function createBranchNameProvider(): ProvideBranchName
    {
        $branchNameProvider = $this->createMock(ProvideBranchName::class);
        $branchNameProvider->method('forTask')
            ->with('Task')
            ->willReturn('task');

        return $branchNameProvider;
    }
}
