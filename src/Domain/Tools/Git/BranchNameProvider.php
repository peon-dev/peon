<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Git;

interface BranchNameProvider
{
    public function provideForTask(string $taskName): string;
}
