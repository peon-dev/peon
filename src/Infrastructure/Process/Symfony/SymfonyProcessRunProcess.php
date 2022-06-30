<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Process\Symfony;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\RunProcess;
use Peon\Domain\Process\Value\ProcessId;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Ui\ReadModel\Process\ReadProcess;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Twig\Environment;

final class SymfonyProcessRunProcess implements RunProcess
{
    public function __construct(
        private HubInterface $hub,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
    )
    {
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

            // Process starting
            $shouldSkipRealtimeLogging = false;

            try {
                $this->hub->publish(
                    new Update(
                        'job-' . $jobId->id . '-detail',
                        $this->twig->render('job/process_started.stream.html.twig', [
                            'process' => new ReadProcess(
                                $processId->id,
                                $jobId->id,
                                $command,
                                $timeoutSeconds,
                                null,
                                null,
                                null,
                            ),
                        ])
                    )
                );
            } catch (\Throwable $throwable) {
                $shouldSkipRealtimeLogging = true;

                $this->logger->warning($throwable->getMessage(), [
                    'exception' => $throwable,
                ]);
            }

            $process->mustRun(function ($type, $buffer) use (&$shouldSkipRealtimeLogging, $jobId, $processId) {
                if ($shouldSkipRealtimeLogging === false) {
                    try {
                        $this->hub->publish(
                            new Update(
                                'job-' . $jobId->id . '-detail',
                                $this->twig->render('job/process_output_buffer.stream.html.twig', [
                                    'buffer' => $buffer,
                                    'processId' => $processId,
                                ])
                            )
                        );
                    } catch (\Throwable $throwable) {
                        $this->logger->warning($throwable->getMessage(), [
                            'exception' => $throwable,
                        ]);

                        $shouldSkipRealtimeLogging = true;
                    }
                }
            });
        } catch (ProcessFailedException $processFailedException) {
            $processResult = $this->createProcessResultFromProcess($processFailedException->getProcess());

            try {
                $this->hub->publish(
                    new Update(
                        'job-' . $jobId->id . '-detail',
                        $this->twig->render('job/process_status_changed.stream.html.twig', [
                            'processId' => $processId->id,
                            'succeeded' => false,
                            'failed' => true,
                            'executionTime' => (int) $processResult->executionTime,
                        ])
                    )
                );
            } catch (\Throwable $throwable) {
                $this->logger->warning($throwable->getMessage(), [
                    'exception' => $throwable,
                ]);
            }

            throw new ProcessFailed($processResult, previous: $processFailedException);
        }

        $processResult = $this->createProcessResultFromProcess($process);

        try {
            $this->hub->publish(
                new Update(
                    'job-' . $jobId->id . '-detail',
                    $this->twig->render('job/process_status_changed.stream.html.twig', [
                        'processId' => $processId->id,
                        'succeeded' => true,
                        'failed' => false,
                        'executionTime' => (int) $processResult->executionTime,
                    ])
                )
            );
        } catch (\Throwable $throwable) {
            $this->logger->warning($throwable->getMessage(), [
                'exception' => $throwable,
            ]);
        }

        return $processResult;
    }


    private function createProcessResultFromProcess(Process $process): ProcessResult
    {
        $output = trim($process->getOutput() . ' ' . $process->getErrorOutput());
        $executionTime = (float) $process->getLastOutputTime() - $process->getStartTime();

        return new ProcessResult((int) $process->getExitCode(), $executionTime, $output);
    }
}
