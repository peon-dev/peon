<?php

declare(strict_types=1);

namespace PHPMate\Domain\GitProvider;


use PHPMate\Domain\Tools\Git\RemoteGitRepository;

interface GitProvider
{
    /**
     * @throws GitProviderCommunicationFailed
     */
    public function openMergeRequest(
        RemoteGitRepository $gitRepository,
        string $targetBranch,
        string $branchWithChanges,
        string $title,
    ): void;

    /**
     * @throws GitProviderCommunicationFailed
     */
    public function hasMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): bool;
}
