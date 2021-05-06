<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Gitlab;

interface Gitlab
{
    public function openMergeRequest(
        GitlabRepository $gitlabRepository,
        string $targetBranch,
        string $branchWithChanges,
        string $title,
    ): void;

    public function mergeRequestForBranchExists(GitlabRepository $gitlabRepository, string $branch): bool;
}
