<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Process;

final class ReadProcess
{
    public readonly int|null $executionTime;

    public function __construct(
        public readonly string $processId,
        public readonly string $jobId,
        public readonly int $sequence,
        public readonly string $command,
        public readonly int $timeoutSeconds,
        float|null $executionTime,
        public int|null $exitCode,
        public string|null $output,
    ) {
        $this->executionTime = $executionTime ? (int) $executionTime : null;
    }
}
