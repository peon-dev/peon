<?php

declare(strict_types=1);

namespace PHPMate\Domain\Process;

interface ProcessResult
{
    public function getExitCode(): int;

    public function getOutput(): string;
}
