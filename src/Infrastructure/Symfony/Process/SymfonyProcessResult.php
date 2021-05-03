<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Symfony\Process;

use PHPMate\Domain\Process\ProcessResult;
use Symfony\Component\Process\Process;

final class SymfonyProcessResult implements ProcessResult
{
    /**
     * @param Process<string> $process
     */
    public function __construct(
        private Process $process
    ) {}


    public function getExitCode(): int
    {
        return (int) $this->process->getExitCode();
    }


    public function getOutput(): string
    {
        return trim($this->process->getOutput() . ' ' . $this->process->getErrorOutput()) ;
    }


    public function getCommand(): string
    {
        return $this->process->getCommandLine();
    }
}
