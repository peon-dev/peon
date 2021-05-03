<?php

declare(strict_types=1);

namespace PHPMate\Domain\Process;

interface ProcessLogger
{
    public function logResult(ProcessResult $processResult): void;
}
