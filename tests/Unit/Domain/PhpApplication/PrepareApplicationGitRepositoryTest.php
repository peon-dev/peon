<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\PhpApplication;

use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\PhpApplication\ApplicationDirectoryProvider;
use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPMate\Domain\Tools\Git\BranchNameProvider;
use PHPMate\Domain\Tools\Git\Exception\GitCommandFailed;
use PHPMate\Domain\Tools\Git\Git;
use PHPUnit\Framework\TestCase;

class PrepareApplicationGitRepositoryTest extends TestCase
{
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
            ->method('checkoutNewBranch')
            ->with('/', 'task');
        $git->expects(self::once())
            ->method('remoteBranchExists')
            ->with('/', 'task')
            ->willReturn(false);

        $projectDirectoryProvider = $this->createMock(ApplicationDirectoryProvider::class);
        $projectDirectoryProvider->expects(self::once())
            ->method('provide')
            ->willReturn('/');

        $branchNameProvider = $this->createMock(BranchNameProvider::class);
        $branchNameProvider->expects(self::once())
            ->method('provideForTask')
            ->with('Task')
            ->willReturn('task');

        $prepareApplicationGitRepository = new PrepareApplicationGitRepository(
            $git,
            $projectDirectoryProvider,
            $branchNameProvider,
        );

        $localApplication = $prepareApplicationGitRepository->prepare(
            $this->getRemoteGitRepository()->getAuthenticatedUri(),
            'Task',
        );

        self::assertSame('main', $localApplication->mainBranch);
        self::assertSame('/', $localApplication->workingDirectory);
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
            ->with('/');
        $git->expects(self::once())
            ->method('rebaseBranchAgainstUpstream')
            ->with('/', 'main');
        $git->expects(self::once())
            ->method('forcePushWithLease');

        $projectDirectoryProvider = $this->createMock(ApplicationDirectoryProvider::class);
        $projectDirectoryProvider->expects(self::once())
            ->method('provide')
            ->willReturn('/');

        $branchNameProvider = $this->createMock(BranchNameProvider::class);

        $prepareApplicationGitRepository = new PrepareApplicationGitRepository(
            $git,
            $projectDirectoryProvider,
            $branchNameProvider,
        );

        $prepareApplicationGitRepository->prepare(
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
            ->willThrowException(new GitCommandFailed());

        $git->expects(self::once())
            ->method('abortRebase');
        $git->expects(self::once())
            ->method('resetCurrentBranch')
            ->with('/', 'main');

        $projectDirectoryProvider = $this->createMock(ApplicationDirectoryProvider::class);
        $projectDirectoryProvider->expects(self::once())
            ->method('provide')
            ->willReturn('/');

        $branchNameProvider = $this->createMock(BranchNameProvider::class);

        $prepareApplicationGitRepository = new PrepareApplicationGitRepository(
            $git,
            $projectDirectoryProvider,
            $branchNameProvider,
        );

        $prepareApplicationGitRepository->prepare(
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
            ->with('/', 'task');

        $projectDirectoryProvider = $this->createMock(ApplicationDirectoryProvider::class);
        $projectDirectoryProvider->expects(self::once())
            ->method('provide')
            ->willReturn('/');

        $branchNameProvider = $this->createBranchNameProvider();

        $prepareApplicationGitRepository = new PrepareApplicationGitRepository(
            $git,
            $projectDirectoryProvider,
            $branchNameProvider,
        );

        $prepareApplicationGitRepository->prepare(
            $this->getRemoteGitRepository()->getAuthenticatedUri(),
            'Task',
        );
    }


    private function getRemoteGitRepository(): RemoteGitRepository
    {
        return new RemoteGitRepository(
            'https://gitlab.com/phpmate/phpmate.git',
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );
    }


    private function createBranchNameProvider(): BranchNameProvider
    {
        $branchNameProvider = $this->createMock(BranchNameProvider::class);
        $branchNameProvider->method('provideForTask')
            ->with('Task')
            ->willReturn('task');

        return $branchNameProvider;
    }
}
