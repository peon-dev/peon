<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\Value\Command;
use Peon\Domain\Process\Value\ProcessOutput;

final class ExecuteCommand
{
    public function __construct(
        private ProcessesCollection    $processesCollection,
        private RunProcess             $runProcess,
        private SanitizeProcessCommand $sanitizeProcessCommand,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function inDirectory(JobId $jobId, string $workingDirectory, string $command, int $timeoutSeconds = 60): ProcessOutput
    {
        // TODO: we will need env variables of project here
        // TODO: if job is canceled, should exit

        $processId = $this->processesCollection->nextIdentity();

        $process = new Process(
            $processId,
            $jobId,
            Command::fromDirty($command, $this->sanitizeProcessCommand),
            $timeoutSeconds,
        );
        $this->processesCollection->save($process);

        try {
            $result = $process->runInDirectory($workingDirectory, $this->runProcess);
        } finally {
            $this->processesCollection->save($process);
        }

        return $result->output;
    }
}
