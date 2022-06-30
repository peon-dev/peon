<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\Value\ProcessResult;

interface RunProcess
{
    /**
     * @throws ProcessFailed
     */
    public function inDirectory(
        string|null $workingDirectory,
        string $command,
        int $timeoutSeconds,
        JobId $jobId,
    ): ProcessResult;
}
