<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

interface GitBinary
{
    public function executeCommand(string $directory, string $command): string;
}
