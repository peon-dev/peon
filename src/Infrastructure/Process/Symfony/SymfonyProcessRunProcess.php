<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Process\Symfony;

use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\RunProcess;
use Peon\Domain\Process\Value\ProcessResult;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SymfonyProcessRunProcess implements RunProcess
{
    /**
     * @throws ProcessFailed
     */
    public function inDirectory(
        string|null $workingDirectory,
        string $command,
        int $timeoutSeconds,
    ): ProcessResult
    {
        try {
            $process = Process::fromShellCommandline($command, $workingDirectory, ['SHELL_VERBOSITY' => 0], timeout: $timeoutSeconds);
            $process->mustRun();
        } catch (ProcessFailedException $processFailedException) {
            $processResult = $this->createProcessResultFromProcess($processFailedException->getProcess());
            throw new ProcessFailed($processResult, previous: $processFailedException);
        }

        return $this->createProcessResultFromProcess($process);
    }


    private function createProcessResultFromProcess(Process $process): ProcessResult
    {
        $output = trim($process->getOutput() . ' ' . $process->getErrorOutput());
        $executionTime = (float) $process->getLastOutputTime() - $process->getStartTime();

        return new ProcessResult((int) $process->getExitCode(), $executionTime, $output);
    }
}
