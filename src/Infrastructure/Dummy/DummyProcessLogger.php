<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Dummy;

use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Process\ProcessResult;

final class DummyProcessLogger implements ProcessLogger
{
    public function logResult(ProcessResult $processResult): void
    {
    }
}
