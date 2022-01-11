<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Job;

use Peon\Domain\Job\Job;
use Peon\Domain\Job\RunJobCommands;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ProcessLogger;
use Peon\Infrastructure\Process\Symfony\SymfonyProcessToProcessResultMapper;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class LoggingSymfonyProcessRunJobCommands implements RunJobCommands
{
    public function __construct(
        private ProcessLogger $processLogger
    )
    {
    }


    /**
     * @throws ProcessFailed
     */
    public function run(Job $job, string $workingDirectory): void
    {
        assert(is_array($job->commands));

        foreach ($job->commands as $jobCommand) {
            // TODO: decouple
            $process = Process::fromShellCommandline($jobCommand, $workingDirectory, timeout: 60 * 20);

            try {
                $process->mustRun();
            } catch (ProcessFailedException $processFailedException) {
                $process = $processFailedException->getProcess();

                throw new ProcessFailed($processFailedException->getMessage(), previous: $processFailedException);
            } finally {
                $processResult = SymfonyProcessToProcessResultMapper::map($process);

                $this->processLogger->logResult($processResult);
            }
        }
    }
}
