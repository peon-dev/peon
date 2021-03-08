<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use PHPMate\Domain\FileSystem\WorkingDirectory;

interface RectorBinary
{
    public function execInDirectory(WorkingDirectory $workingDirectory, string $command): void;
}
