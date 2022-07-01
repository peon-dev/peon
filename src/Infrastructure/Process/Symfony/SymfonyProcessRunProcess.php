<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Process\Symfony;

use Peon\Domain\Job\Event\JobProcessFinished;
use Peon\Domain\Job\Event\JobProcessOutputReceived;
use Peon\Domain\Job\Event\JobProcessStarted;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\RunProcess;
use Peon\Domain\Process\Value\ProcessId;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SymfonyProcessRunProcess implements RunProcess
{
    public function __construct(
        private readonly EventBus $eventBus,
    ) {
    }


    /**
     * @throws ProcessFailed
     */
    public function inDirectory(
        string|null $workingDirectory,
        string $command,
        int $timeoutSeconds,
        JobId $jobId,
        ProcessId $processId,
    ): ProcessResult
    {
        try {
            $process = Process::fromShellCommandline($command, $workingDirectory, ['SHELL_VERBOSITY' => 0], timeout: $timeoutSeconds);

            $this->eventBus->dispatch(
                new JobProcessStarted(
                    $jobId,
                    $processId,
                    $command,
                )
            );

            $process->mustRun(function ($type, $buffer) use ($jobId, $processId) {
                $this->eventBus->dispatch(
                    new JobProcessOutputReceived(
                        $jobId,
                        $processId,
                        $buffer,
                    ),
                );
            });
        } catch (ProcessFailedException $processFailedException) {
            $processResult = $this->createProcessResultFromProcess($processFailedException->getProcess());

            $this->eventBus->dispatch(
                new JobProcessFinished(
                    $jobId,
                    $processId,
                    false,
                    (int) $processResult->executionTime,
                ),
            );

            throw new ProcessFailed($processResult, previous: $processFailedException);
        }

        $processResult = $this->createProcessResultFromProcess($process);

        $this->eventBus->dispatch(
            new JobProcessFinished(
                $jobId,
                $processId,
                true,
                (int) $processResult->executionTime,
            ),
        );

        return $processResult;
    }


    private function createProcessResultFromProcess(Process $process): ProcessResult
    {
        $output = trim($process->getOutput() . ' ' . $process->getErrorOutput());
        $executionTime = (float) $process->getLastOutputTime() - $process->getStartTime();

        return new ProcessResult((int) $process->getExitCode(), $executionTime, $output);
    }
}
