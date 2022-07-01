<?php

declare(strict_types=1);

namespace Peon\Infrastructure\GitProvider;

use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

final class GitHub implements GitProvider
{
    public function openMergeRequest(RemoteGitRepository $gitRepository, string $targetBranch, string $branchWithChanges, string $title, string $description,): MergeRequest
    {
        // TODO: Implement openMergeRequest() method.
    }

    public function getMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): MergeRequest|null
    {
        // TODO: Implement getMergeRequestForBranch() method.
    }

    public function isAutoMergeSupported(): bool
    {
        // TODO: Implement isAutoMergeSupported() method.
    }

    public function mergeAutomatically(RemoteGitRepository $gitRepository, MergeRequest $mergeRequest): void
    {
        // TODO: Implement mergeAutomatically() method.
    }

    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit
    {
        // TODO: Implement getLastCommitOfDefaultBranch() method.
    }

    public function hasWriteAccessToRepository(RemoteGitRepository $gitRepository): bool
    {
        // TODO: Implement hasWriteAccessToRepository() method.
    }
}
