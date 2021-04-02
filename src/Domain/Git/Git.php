<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;

final class Git
{
    public function __construct(
        private GitBinary $gitBinary
    ) {}


    public function clone(WorkingDirectory $workingDirectory, string $remoteUri): void
    {
        // TODO
       $this->gitBinary->execInDirectory($workingDirectory, '');
    }

    public function hasUncommittedChanges(WorkingDirectory $workingDirectory): bool
    {
        // TODO
        $this->gitBinary->execInDirectory($workingDirectory, '');

        return false;
    }

    public function checkoutNewBranch(WorkingDirectory $workingDirectory, string $branch): void
    {
        // TODO
        $this->gitBinary->execInDirectory($workingDirectory, '');
    }

    public function commitAndPushChanges(WorkingDirectory $workingDirectory, string $commitMessage): void
    {
        // TODO
        $this->gitBinary->execInDirectory($workingDirectory, '');
    }
}
