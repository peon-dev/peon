<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Symfony\Process;

use PHPMate\Domain\Process\ProcessResult;
use Symfony\Component\Process\Process;

final class SymfonyProcessResult implements ProcessResult
{
    public function __construct(
        private Process $process
    ) {}


    public function getExitCode(): int
    {
        return $this->process->getExitCode();
    }

    public function getOutput(): string
    {
        return $this->process->getOutput();
    }

    public function getErrorOutput(): string
    {
        return $this->process->getErrorOutput();
    }
}
