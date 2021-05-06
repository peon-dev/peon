<?php

declare(strict_types=1);

namespace PHPMate\Domain\Process;

final class ProcessLogger
{
    /**
     * @var ProcessResult[]
     */
    private array $logs = [];


    public function logResult(ProcessResult $processResult): void
    {
        $this->logs[] = $processResult;
    }

    /**
     * @return ProcessResult[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
