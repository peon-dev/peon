<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\Value\ProcessId;
use Peon\Domain\Process\Value\ProcessResult;

class Process
{
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public float|null $executionTime = null;

    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public int|null $exitCode = null;

    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public string|null $output = null;


    public function __construct(
        public readonly ProcessId $processId,
        public readonly JobId $jobId,
        public readonly int $sequence,
        public readonly string $command,
        public readonly int $timeoutSeconds,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function runInDirectory(string $directory, RunProcess $runProcess): ProcessResult
    {
        try {
            $result = $runProcess->inDirectory($directory, $this->command, $this->timeoutSeconds);
            $this->exitCode = $result->exitCode;
            $this->executionTime = $result->executionTime;
            $this->output = $result->output;

            return $result;
        } catch (ProcessFailed $processFailed) {
            $result = $processFailed->result;
            $this->exitCode = $result->exitCode;
            $this->executionTime = $result->executionTime;
            $this->output = $result->output;

            throw $processFailed;
        }
    }
}
