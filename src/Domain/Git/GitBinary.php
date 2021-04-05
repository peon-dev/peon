<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

interface GitBinary
{
    public function execInDirectory(string $workingDirectory, string $command): string;
}
