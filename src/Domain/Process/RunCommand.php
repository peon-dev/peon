<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\Value\SanitizedCommand;

final class RunCommand
{
    public function __construct(
        private ProcessesCollection $processesCollection,
        private ExecuteProcess $executeProcess,
        private AppendProcessOutput $appendProcessOutput,
        private SanitizeProcessCommand $sanitizeProcessCommand,
        private JobsCollection $jobsCollection,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function inDirectory(Job $job, string $workingDirectory, string $command, int $timeoutSeconds = 60): void
    {
        $processId = $this->processesCollection->nextIdentity();

        $process = new Process(
            $processId,
            new SanitizedCommand($command, $this->sanitizeProcessCommand),
            $timeoutSeconds,
        );
        $this->processesCollection->save($process);

        $job->addProcessId($processId);
        $this->jobsCollection->save($job);

        $result = $this->executeProcess->inDirectory($workingDirectory, $process, $this->appendProcessOutput);

        $process->finish($result->exitCode, $result->executionTime);
        $this->processesCollection->save($process);
    }
}
