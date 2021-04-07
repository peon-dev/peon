<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;

interface GitBinary
{
    public function execInWorkingDirectory(WorkingDirectory $workingDirectory, string $command): string;
}
