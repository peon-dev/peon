<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Git;

interface BranchNameProvider
{
    public function provideForTask(string $taskName): string;
}
