<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Git;

interface ProvideBranchName
{
    public function forTask(string $taskName): string;
}
