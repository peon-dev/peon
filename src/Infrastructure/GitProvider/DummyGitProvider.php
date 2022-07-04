<?php

declare(strict_types=1);

namespace Peon\Infrastructure\GitProvider;

use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

final class DummyGitProvider implements GitProvider
{
    public function openMergeRequest(RemoteGitRepository $gitRepository, string $targetBranch, string $branchWithChanges, string $title, string $description,): MergeRequest
    {
        return new MergeRequest('', '');
    }

    public function getMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): MergeRequest|null
    {
        return null;
    }

    public function isAutoMergeSupported(RemoteGitRepository $gitRepository): bool
    {
        return true;
    }

    public function mergeAutomatically(RemoteGitRepository $gitRepository, MergeRequest $mergeRequest): void
    {
    }

    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit
    {
        return new Commit('12345');
    }

    public function hasWriteAccessToRepository(RemoteGitRepository $gitRepository): bool
    {
        return true;
    }
}
