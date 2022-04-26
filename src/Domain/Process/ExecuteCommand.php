<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\Value\ProcessResult;
use SplObjectStorage;

class ExecuteCommand
{
    /**
     * @var SplObjectStorage<JobId, int>
     */
    private SplObjectStorage $sequences;


    public function __construct(
        private ProcessesCollection    $processesCollection,
        private RunProcess             $runProcess,
    ) {
        $this->sequences = new SplObjectStorage(); // TODO: this makes the class stateful, maybe find better way in the future
    }


    /**
     * @throws ProcessFailed
     */
    public function inContainer(
        JobId $jobId,
        string $image,
        string $applicationHostPath,
        string $command,
        int $timeoutSeconds = 300,
    ): string
    {
        $dockerCommand = sprintf('docker run --workdir=/app --rm --volume=%s:/app %s bash -c "%s"',
            $applicationHostPath,
            $image,
            $command,
        );

        $result = $this->doExecuteCommand($jobId, $dockerCommand, $timeoutSeconds);

        return $result->output;
    }


    /**
     * @throws ProcessFailed
     */
    public function inDirectory(JobId $jobId, string $workingDirectory, string $command, int $timeoutSeconds = 300): string
    {
        $result = $this->doExecuteCommand($jobId, $command, $timeoutSeconds, $workingDirectory);

        return $result->output;
    }


    private function getNextSequenceForProcessOfJob(JobId $jobId): int
    {
        $current = $this->sequences[$jobId] ?? 0;
        $next = $current + 1;

        $this->sequences[$jobId] = $next;

        return $next;
    }


    /**
     * @throws ProcessFailed
     */
    private function doExecuteCommand(JobId $jobId, string $command, int $timeoutSeconds, string|null $workingDirectory = null): ProcessResult
    {
        // TODO: we will need env variables of project here
        // TODO: if job is canceled, should exit

        $processId = $this->processesCollection->nextIdentity();

        $process = new Process(
            $processId,
            $jobId,
            $this->getNextSequenceForProcessOfJob($jobId),
            $command,
            $timeoutSeconds,
        );
        $this->processesCollection->save($process);

        try {
            $result = $process->runInDirectory($workingDirectory, $this->runProcess);
        } finally {
            $this->processesCollection->save($process);
        }

        return $result;
    }
}
