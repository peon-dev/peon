<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Job\Value\JobId;

final class ExecuteRecipeJob
{
    public function __construct(
        public readonly JobId $jobId
    ) {}
}
