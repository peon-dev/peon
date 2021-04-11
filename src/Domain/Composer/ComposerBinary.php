<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

use PHPMate\Domain\FileSystem\WorkingDirectory;

interface ComposerBinary
{
    public function executeCommand(WorkingDirectory $projectDirectory, string $command): void;
}
