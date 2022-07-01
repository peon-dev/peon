<?php

declare(strict_types=1);

namespace Peon\Domain\GitProvider;


use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

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
        string $description,
    ): MergeRequest;

    /**
     * @throws GitProviderCommunicationFailed
     */
    public function getMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): MergeRequest|null;

    public function isAutoMergeSupported(): bool;

    /**
     * @throws GitProviderCommunicationFailed
     */
    public function mergeAutomatically(RemoteGitRepository $gitRepository, MergeRequest $mergeRequest): void;

    /**
     * @throws GitProviderCommunicationFailed
     */
    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit;

    /**
     * @throws GitProviderCommunicationFailed
     */
    public function hasWriteAccessToRepository(RemoteGitRepository $gitRepository): bool;
}
