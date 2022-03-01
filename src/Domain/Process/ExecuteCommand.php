<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
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


    public function inDocker(JobId $jobId, string $workingDirectory, string $command, int $timeoutSeconds = 300): string
    {
        // TODO: we will need env variables of project here
        // TODO: if job is canceled, should exit

        $processId = $this->processesCollection->nextIdentity();

        $dockerCommand = 'wrap this with docker';

        $process = new Process(
            $processId,
            $jobId,
            $this->getNextSequenceForProcessOfJob($jobId),
            $dockerCommand,
            $timeoutSeconds,
        );
        $this->processesCollection->save($process);

        // TODO, will we here output?

        try {
            $result = $process->runInDirectory($workingDirectory, $this->runProcess);
        } finally {
            $this->processesCollection->save($process);
        }

        return $result->output;
    }


    /**
     * @throws ProcessFailed
     */
    public function inDirectory(JobId $jobId, string $workingDirectory, string $command, int $timeoutSeconds = 300): string
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

        return $result->output;
    }


    private function getNextSequenceForProcessOfJob(JobId $jobId): int
    {
        $current = $this->sequences[$jobId] ?? 0;
        $next = $current + 1;

        $this->sequences[$jobId] = $next;

        return $next;
    }
}
