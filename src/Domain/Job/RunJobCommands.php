<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use PHPMate\Domain\Process\Exception\ProcessFailed;

interface RunJobCommands
{
    /**
     * @throws ProcessFailed
     */
    public function run(Job $job, string $workingDirectory): void;
}
