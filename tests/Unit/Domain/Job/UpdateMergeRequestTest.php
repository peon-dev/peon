<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Job;

use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Tools\Git\Git;
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
        $jobId = new JobId('');

        $mergeRequest = $updateMergeRequest->update(
            $jobId,
            $this->getTemporaryApplication($jobId),
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
        $jobId = new JobId('');

        $mergeRequest = $updateMergeRequest->update(
            $jobId,
            $this->getTemporaryApplication($jobId),
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
        $jobId = new JobId('');

        $mergeRequest = $updateMergeRequest->update(
            $jobId,
            $this->getTemporaryApplication($jobId),
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
        $jobId = new JobId('');

        $mergeRequest = $updateMergeRequest->update(
            $jobId,
            $this->getTemporaryApplication($jobId),
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
        $jobId = new JobId('');

        $mergeRequest = $updateMergeRequest->update(
            $jobId,
            $this->getTemporaryApplication($jobId),
            $this->getRemoteGitRepository(),
            'Title',
            true
        );

        self::assertNull($mergeRequest);
    }


    private function getTemporaryApplication(JobId $jobId): TemporaryApplication
    {
        return new TemporaryApplication(
            $jobId,
            '/',
            'main',
            'job',
        );
    }


    private function getRemoteGitRepository(): RemoteGitRepository
    {
        return new RemoteGitRepository(
            'https://gitlab.com/peon/peon.git',
            GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
        );
    }
}
