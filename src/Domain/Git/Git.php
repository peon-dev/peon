<?php

declare(strict_types=1);

namespace Acme\Domain\Git;

interface Git
{
    public function clone(string $directory, string $remoteUri): void;

    public function hasUncommittedChanges(string $directory): bool;

    public function checkoutNewBranch(string $directory, string $branch): bool;

    public function commitChanges(string $directory, string $commitMessage);
}
