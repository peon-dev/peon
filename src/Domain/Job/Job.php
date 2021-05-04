<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use PHPMate\Domain\Process\ProcessResult;

final class Job
{
    /**
     * @var ProcessResult[]
     */
    private array $logs = [];


    public function __construct(
        public int $timestamp,
    ) {}


    public function addLog(ProcessResult $processResult): void
    {
        $this->logs[] = $processResult;
    }
}
