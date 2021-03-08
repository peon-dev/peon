<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use League\Flysystem\FilesystemReader;

interface Git
{
    public function clone(FilesystemReader $workingDirectory, string $remoteUri): void;

    public function hasUncommittedChanges(FilesystemReader $workingDirectory): bool;

    public function checkoutNewBranch(FilesystemReader $workingDirectory, string $branch): void;

    public function commitAndPushChanges(FilesystemReader $workingDirectory, string $commitMessage): void;
}
