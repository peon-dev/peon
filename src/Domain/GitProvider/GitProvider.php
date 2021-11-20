<?php

declare(strict_types=1);

namespace PHPMate\Domain\GitProvider;


use PHPMate\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use PHPMate\Domain\Tools\Git\Value\RemoteGitRepository;

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
