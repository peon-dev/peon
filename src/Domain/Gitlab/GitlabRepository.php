<?php

declare(strict_types=1);

namespace Acme\Domain\Gitlab;

interface GitlabRepository
{
    public function checkout(string $gitReference): void;

    public function checkoutGitReference(string $string): void;

    // composer.json might be missing
    public function installComposer();

    // rector package might not be installed
    // rector config might be missing
    public function runRector();

    public function hasUncommittedChanges(): bool;

    public function checkoutNewGitBranch(string $localHead): void;

    public function commitAndPushChanges(): void;
}
