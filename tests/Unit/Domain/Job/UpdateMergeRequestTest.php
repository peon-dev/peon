<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Job;

use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\Tools\Git\Git;
use Peon\Tests\DataFixtures\TestDataFactory;
use PHPUnit\Framework\TestCase;

final class UpdateMergeRequestTest extends TestCase
{
    public function testLocalChangesNewMergeRequestShouldBeOpened(): void
    {
        $fakeMergeRequest = new MergeRequest('', '');

        $gitProvider = $this->createMock(GitProvider::class);
        $gitProvider->expects(self::once())
            ->method('getMergeRequestForBranch')
            ->willReturn(null);
        $gitProvider->expects(self::once())
            ->method('openMergeRequest')
            ->willReturn($fakeMergeRequest);
        $gitProvider->expects(self::once())
            ->method('isAutoMergeSupported')
            ->willReturn(true);
        $gitProvider->expects(self::once())
            ->method('mergeAutomatically');

        $git = $this->createMock(Git::class);
        $git->expects(self::once())
            ->method('hasUncommittedChanges')
            ->willReturn(true);
        $git->expects(self::once())
            ->method('commit');
        $git->expects(self::once())
            ->method('forcePushWithLease');
        $git->expects(self::never())
            ->method('remoteBranchExists');

        $updateMergeRequest = new UpdateMergeRequest($gitProvider, $git);
        $temporaryApplication = TestDataFactory::createTemporaryApplication();

        $mergeRequest = $updateMergeRequest->update(
            $temporaryApplication->jobId,
            $temporaryApplication->gitRepository,
            $this->getRemoteGitRepository(),
            'Title',
            true
        );

        self::assertSame($fakeMergeRequest, $mergeRequest);
    }


    public function testLocalChangesMergeRequestAlreadyOpened(): void
    {
        $fakeMergeRequest = new MergeRequest('', '');

        $gitProvider = $this->createMock(GitProvider::class);
        $gitProvider->expects(self::once())
            ->method('getMergeRequestForBranch')
            ->willReturn($fakeMergeRequest);
        $gitProvider->expects(self::never())
            ->method('openMergeRequest');
        $gitProvider->expects(self::once())
            ->method('mergeAutomatically');

        $git = $this->createMock(Git::class);
        $git->expects(self::once())
            ->method('hasUncommittedChanges')
            ->willReturn(true);
        $git->expects(self::once())
            ->method('commit');
        $git->expects(self::once())
            ->method('forcePushWithLease');
        $git->expects(self::never())
            ->method('remoteBranchExists');

        $updateMergeRequest = new UpdateMergeRequest($gitProvider, $git);
        $temporaryApplication = TestDataFactory::createTemporaryApplication();

        $mergeRequest = $updateMergeRequest->update(
            $temporaryApplication->jobId,
            $temporaryApplication->gitRepository,
            $this->getRemoteGitRepository(),
            'Title',
            true
        );

        self::assertSame($fakeMergeRequest, $mergeRequest);
    }


    public function testNoChangesRemoteBranchExistsNewMergeRequestShouldBeOpened(): void
    {
        $fakeMergeRequest = new MergeRequest('', '');

        $gitProvider = $this->createMock(GitProvider::class);
        $gitProvider->expects(self::once())
            ->method('getMergeRequestForBranch')
            ->willReturn($fakeMergeRequest);
        $gitProvider->expects(self::never())
            ->method('openMergeRequest');
        $gitProvider->expects(self::once())
            ->method('mergeAutomatically');

        $git = $this->createMock(Git::class);
        $git->expects(self::once())
            ->method('hasUncommittedChanges')
            ->willReturn(false);
        $git->expects(self::once())
            ->method('remoteBranchExists')
            ->willReturn(true);

        $updateMergeRequest = new UpdateMergeRequest($gitProvider, $git);
        $temporaryApplication = TestDataFactory::createTemporaryApplication();

        $mergeRequest = $updateMergeRequest->update(
            $temporaryApplication->jobId,
            $temporaryApplication->gitRepository,
            $this->getRemoteGitRepository(),
            'Title',
            true
        );

        self::assertSame($fakeMergeRequest, $mergeRequest);
    }


    public function testNoLocalChangesRemoteBranchExistsMergeRequestAlreadyOpened(): void
    {
        $fakeMergeRequest = new MergeRequest('', '');

        $gitProvider = $this->createMock(GitProvider::class);
        $gitProvider->expects(self::once())
            ->method('getMergeRequestForBranch')
            ->willReturn(null);
        $gitProvider->expects(self::once())
            ->method('openMergeRequest')
            ->willReturn($fakeMergeRequest);
        $gitProvider->expects(self::once())
            ->method('mergeAutomatically');

        $git = $this->createMock(Git::class);
        $git->expects(self::once())
            ->method('hasUncommittedChanges')
            ->willReturn(false);
        $git->expects(self::once())
            ->method('remoteBranchExists')
            ->willReturn(true);

        $updateMergeRequest = new UpdateMergeRequest($gitProvider, $git);
        $temporaryApplication = TestDataFactory::createTemporaryApplication();

        $mergeRequest = $updateMergeRequest->update(
            $temporaryApplication->jobId,
            $temporaryApplication->gitRepository,
            $this->getRemoteGitRepository(),
            'Title',
            true
        );

        self::assertSame($fakeMergeRequest, $mergeRequest);
    }


    public function testNoLocalChangesNoRemoteBranchNothingHappens(): void
    {
        $gitProvider = $this->createMock(GitProvider::class);
        $gitProvider->expects(self::never())
            ->method('getMergeRequestForBranch');
        $gitProvider->expects(self::never())
            ->method('mergeAutomatically');

        $git = $this->createMock(Git::class);
        $git->expects(self::once())
            ->method('hasUncommittedChanges')
            ->willReturn(false);
        $git->expects(self::once())
            ->method('remoteBranchExists')
            ->willReturn(false);

        $updateMergeRequest = new UpdateMergeRequest($gitProvider, $git);

        $temporaryApplication = TestDataFactory::createTemporaryApplication();

        $mergeRequest = $updateMergeRequest->update(
            $temporaryApplication->jobId,
            $temporaryApplication->gitRepository,
            $this->getRemoteGitRepository(),
            'Title',
            true
        );

        self::assertNull($mergeRequest);
    }


    private function getRemoteGitRepository(): RemoteGitRepository
    {
        return new RemoteGitRepository(
            'https://gitlab.com/peon/peon.git',
            GitRepositoryAuthentication::fromGitLabPersonalAccessToken('PAT')
        );
    }
}
