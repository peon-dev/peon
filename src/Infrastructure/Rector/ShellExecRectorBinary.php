<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Rector;

use PHPMate\Domain\FileSystem\WorkingDirectory;
use PHPMate\Domain\Rector\RectorBinary;

final class ShellExecRectorBinary implements RectorBinary
{
    public function execInDirectory(WorkingDirectory $workingDirectory, string $command): void
    {
        $command = sprintf('cd %s && %s', $workingDirectory->getPath(), $command);

        shell_exec($command);
    }
}
