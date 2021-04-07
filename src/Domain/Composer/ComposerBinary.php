<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

interface ComposerBinary
{
    public function execInWorkingDirectory(string $workingDirectory, string $command): void;
}
