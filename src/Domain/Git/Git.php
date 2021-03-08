<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

interface Git
{
    public function clone(string $directory, string $remoteUri): void;

    public function hasUncommittedChanges(string $directory): bool;

    public function checkoutNewBranch(string $directory, string $branch): void;

    public function commitAndPushChanges(string $directory, string $commitMessage): void;
}
