<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

interface ComposerBinary
{
    public function execInDirectory(string $workingDirectory, string $command): void;
}
