<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

interface RectorBinary
{
    public function executeCommand(string $directory, string $command): void;
}
