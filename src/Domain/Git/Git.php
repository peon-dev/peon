<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;

interface Git
{
    public function clone(WorkingDirectory $workingDirectory, string $remoteUri): void;

    public function hasUncommittedChanges(WorkingDirectory $workingDirectory): bool;

    public function checkoutNewBranch(WorkingDirectory $workingDirectory, string $branch): void;

    public function commitAndPushChanges(WorkingDirectory $workingDirectory, string $commitMessage): void;
}
