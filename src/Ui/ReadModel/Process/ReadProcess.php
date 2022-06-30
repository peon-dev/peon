<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Process;

final class ReadProcess
{
    public readonly int|null $executionTime;

    public function __construct(
        public readonly string $processId,
        public readonly string $jobId,
        public readonly string $command,
        public readonly int $timeoutSeconds,
        float|null $executionTime,
        public int|null $exitCode,
        public string|null $output,
    ) {
        $this->executionTime = $executionTime ? (int) $executionTime : null;
    }


    public function hasSucceeded(): bool
    {
        return $this->exitCode === 0;
    }


    public function hasFailed(): bool
    {
        return $this->exitCode > 0;
    }
}
