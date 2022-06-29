<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Process\Symfony;

use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\RunProcess;
use Peon\Domain\Process\Value\ProcessResult;
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
    ): ProcessResult
    {
        try {
            $process = Process::fromShellCommandline($command, $workingDirectory, ['SHELL_VERBOSITY' => 0], timeout: $timeoutSeconds);
            $shouldSkipRealtimeLogging = false;
            $this->hub->publish(
                new Update(
                    'event-stream',
                    $this->twig->render('job/process_started.stream.html.twig', [
                        'process' => $command,
                    ])
                )
            );
            $process->mustRun(function ($type, $buffer) use (&$shouldSkipRealtimeLogging) {
                if ($shouldSkipRealtimeLogging === false) {
                    try {
                        $this->hub->publish(
                            new Update(
                                'event-stream',
                                $this->twig->render('job/process_output_buffer.stream.html.twig', [
                                    'buffer' => $buffer,
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
            throw new ProcessFailed($processResult, previous: $processFailedException);
        } finally {
            $this->hub->publish(
                new Update(
                    'event-stream',
                    $this->twig->render('job/process_finished.stream.html.twig', [
                        'process' => $command,
                    ])
                )
            );
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
