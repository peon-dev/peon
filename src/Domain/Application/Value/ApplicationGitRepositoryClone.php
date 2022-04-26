<?php

declare(strict_types=1);

namespace Peon\Domain\Application\Value;

final class ApplicationGitRepositoryClone
{
    public function __construct(
        public readonly WorkingDirectory $workingDirectory,
        public readonly string $mainBranch,
        public readonly string $jobBranch
    ) {}
}
