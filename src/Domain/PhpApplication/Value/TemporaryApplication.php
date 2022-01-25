<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication\Value;

use Peon\Domain\Job\Value\JobId;

final class TemporaryApplication
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly string $workingDirectory,
        public readonly string $mainBranch,
        public readonly string $jobBranch
    ) {}
}
