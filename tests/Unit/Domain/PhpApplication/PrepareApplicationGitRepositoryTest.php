<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\PhpApplication;

use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\PhpApplication\ApplicationDirectoryProvider;
use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPMate\Domain\Tools\Git\BranchNameProvider;
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
        $git->expects(self::exactly(2)) // TODO: SHOULD BE ONLY ONCE!!
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
    }


    public function testRemoteBranchExistsRebaseFailedBranchWillBeReset(): void
    {
    }


    public function testRemoteBranchExistsAndWillBeCheckedOut(): void
    {
    }


    public function testRemoteBranchNotExistsAndNewWillBeCheckedOut(): void
    {
    }


    private function getRemoteGitRepository(): RemoteGitRepository
    {
        return new RemoteGitRepository(
            'https://gitlab.com/phpmate/phpmate.git',
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );
    }
}
