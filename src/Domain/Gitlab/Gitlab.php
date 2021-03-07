<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

interface Gitlab
{
    public function openMergeRequest(GitlabRepository $gitlabRepository, string $branchWithChanges): void;
}
