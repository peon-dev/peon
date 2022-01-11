<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Peon\Domain\Process\Exception\ProcessFailed;

interface RunJobCommands
{
    /**
     * @throws ProcessFailed
     */
    public function run(Job $job, string $workingDirectory): void;
}
