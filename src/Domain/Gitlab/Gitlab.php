<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

interface Gitlab
{
    public function openMergeRequest(string $repositoryName, string $personalAccessToken, string $branchWithChanges): void;
}
