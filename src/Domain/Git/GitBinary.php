<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;

interface GitBinary
{
    public function executeCommand(WorkingDirectory $projectDirectory, string $command): string;
}
