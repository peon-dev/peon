<?php

declare(strict_types=1);

namespace PHPMate\Domain\Gitlab;

interface Gitlab
{
    public function openMergeRequest($repositoryName, $personalAccessToken, $branchWithChanges): void;
}
