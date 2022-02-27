<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Job;

use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\PhpApplication\Value\TemporaryApplication;
use Peon\Domain\Tools\Git\Git;
use PHPUnit\Framework\TestCase;

final class UpdateMergeRequestTest extends TestCase
{
    public function testLocalChangesNewMergeRequestShouldBeOpened(): void
    {
        $gitProvider = $this->createMock(GitProvider::class);
        $git = $this->createMock(Git::class);

        $updateMergeRequest = new UpdateMergeRequest($gitProvider, $git);

        $mergeRequest = $updateMergeRequest->update(
            new JobId(''),
            new TemporaryApplication(
                new JobId(''),
                '/',
                'main',
                'job',
            ),
            new RemoteGitRepository('https://gitlab.com/peon/peon.git', GitRepositoryAuthentication::fromPersonalAccessToken('PAT')),
            'Title',
            false
        );

        self::assertNotNull($mergeRequest);
    }


    public function testLocalChangesMergeRequestAlreadyOpened(): void
    {
    }


    public function testNoChangesRemoteBranchExistsNewMergeRequestShouldBeOpened(): void
    {
    }


    public function testNoChangesRemoteBranchExistsMergeRequestAlreadyOpened(): void
    {
    }


    public function testNoLocalChangesNoRemoteBranchNothingHappens(): void
    {
    }
}
